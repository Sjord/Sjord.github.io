---
layout: post
title: "Avoid passing secrets in build arguments in Docker"
thumbnail: container-in-forest-480.jpg
date: 2022-08-31
---

While building a Docker image, arguments can be passed using the `ARG` keyword and `--build-arg` option. These should not be used for secrets, because the build arguments end up in the history for the image. 

## Introduction

Docker is not only useful to provide an environment for running your application, but also for building your application. Especially in CI/CD pipelines, it's easy to use a Docker image with the correct compilers and frameworks to build the application. The build process may need to retrieve further information or dependencies from other services during building. A typical example is when a private NuGet or NPM repository is used to fetch dependencies from. If these services need authentication, the Docker image need access to credentials. We want to keep these credentials secret, so it's important to pass them correctly to Docker.

## Docker build time arguments

The Dockerfile specifies possible arguments with the [`ARG` keyword](https://docs.docker.com/engine/reference/builder/#arg). During build, arguments can be supplied using `--build-arg KEY=VALUE`. The specified argument becomes a environment variable during the build process.

Docker build time arguments are not suited for secrets, because the argument values are saved with the image. Running `docker image history` on the image will show information on how the image was built, including arguments. If these contain secrets, anyone with access to the docker image can access those secrets.

Who can access the Docker image? Anyone with access to the Docker host, so that would be anyone with Docker permissions on the build server. If the image is pushed to a registry, the history is also included, which means that anyone who can pull the image from the registry has access to the secret.

## Example

Dockerfile:

    FROM alpine
    ARG mysecret
    RUN echo "Secret during build time: $mysecret"
    CMD echo "Secret during run time: $mysecret"

Build command:

    $ docker build --build-arg mysecret=123123123 .
    ...
    Step 3/4 : RUN echo "Secret during build time: $mysecret"
    Secret during build time: 123123123
    ...
    Successfully built 84e0f4916ec7

The secret is not available when running the image:

    $ docker run 84e0f4916ec7
    Secret during run time:

But the image history shows the secret argument:

    $ docker image history 84e0f4916ec7
    IMAGE          CREATED              CREATED BY                                      SIZE      COMMENT
    84e0f4916ec7   About a minute ago   /bin/sh -c #(nop)  CMD ["/bin/sh" "-c" "echo…   0B
    173595f89c3c   About a minute ago   |1 mysecret=123123123 /bin/sh -c echo "Secre…   0B
    59f9d5ceccc4   About a minute ago   /bin/sh -c #(nop)  ARG mysecret                 0B
    9c6f07244728   2 weeks ago          /bin/sh -c #(nop)  CMD ["/bin/sh"]              0B
    <missing>      2 weeks ago          /bin/sh -c #(nop) ADD file:2a949686d9886ac7c…   5.54MB

## Rewriting history

The history is saved by Docker somewhere on the disk. Can we modify that file to remove our secret from the history?

Searching for `123123123` in `/var/lib/docker` gives a match, on a file with a name that matches the hash of our image: `/var/lib/docker/image/overlay2/imagedb/content/sha256/84e0f4916ec7a3d59cf97721638e9f2f5d10471116b741af25b7ecd1eb554c4d`. This is a JSON file with the build properties of our image:

    {
        "architecture" : "amd64",
        ...
        "history" : [
        ...
        {
            "created" : "2022-08-30T09:13:06.050542585Z",
            "created_by" : "|1 mysecret=123123123 /bin/sh -c echo \"Secret during build time: $mysecret\"",
            "empty_layer" : true
        }
        ...
    }

Simply editing this file doesn't work. If we change `mysecret=123123123` to `mysecret=xxx`, in an attempt to remove our secret from the history, the image breaks. When we check whether it worked, the image is gone:

    $ docker image history 84e0f4916ec7
    Error response from daemon: No such image: 84e0f4916ec7:latest

By changing the file, we also modified the SHA256 hash of the file. The filename Docker uses is the SHA256 of the contents, and this identifies the image. Passing another build argument automatically changes the hash of the image, and thus results in another image. The build argument is part of the identity of the image.

Fine, we'll rename the file:

    $ sha256sum 84e0f4916ec7a3d59cf97721638e9f2f5d10471116b741af25b7ecd1eb554c4d
    b1ca8a297ccb6aff230ad1bf740193f449cc1e00fd2225b8769290071ce4c041  84e0f4916ec7a3d59cf97721638e9f2f5d10471116b741af25b7ecd1eb554c4d
    $ mv 84e0f4916ec7a3d59cf97721638e9f2f5d10471116b741af25b7ecd1eb554c4d b1ca8a297ccb6aff230ad1bf740193f449cc1e00fd2225b8769290071ce4c041
    $ docker history b1ca8a297ccb6aff230ad1bf740193f449cc1e00fd2225b8769290071ce4c041
    IMAGE          CREATED          CREATED BY                                      SIZE      COMMENT
    b1ca8a297ccb   13 minutes ago   /bin/sh -c #(nop)  CMD ["/bin/sh" "-c" "echo…   0B
    <missing>      13 minutes ago   |1 mysecret=xxx /bin/sh -c echo "Secret duri…   0B
    <missing>      13 minutes ago   /bin/sh -c #(nop)  ARG mysecret                 0B
    <missing>      2 weeks ago      /bin/sh -c #(nop)  CMD ["/bin/sh"]              0B
    <missing>      2 weeks ago      /bin/sh -c #(nop) ADD file:2a949686d9886ac7c…   5.54MB

Docker becomes a little bit confused that we are modifying the database under its nose, but the image still runs and its history no longer contains the secret.

## Conclusion

Build time arguments end up in the image's history, and thus are not suitable for secrets. The image's history, including the build time arguments, are part of the image's identity: a different argument value would result in a different image. Therefore, it makes sense to include the arguments in the history, which is unfortunate for the confidentiality of secrets.

## Read more

* [Rule to detect secrets in build time arguments in Docker by Sjord · Pull Request #2363 · returntocorp/semgrep-rules](https://github.com/returntocorp/semgrep-rules/pull/2363)
* [Predefined build-time arguments persisting in the resulting image. · Issue #30721 · moby/moby](https://github.com/moby/moby/issues/30721)
* [Exclude “default” build-args from image history by dave-tucker · Pull Request #31584 · moby/moby](https://github.com/moby/moby/pull/31584)
* [Add "secret" as allowed build args by manabusakai · Pull Request #36443 · moby/moby](https://github.com/moby/moby/pull/36443)
* [Build Secrets by ehazlett · Pull Request #30637 · moby/moby](https://github.com/moby/moby/pull/30637)
