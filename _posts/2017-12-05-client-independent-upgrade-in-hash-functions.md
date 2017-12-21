---
layout: post
title: "Upgrading a password hash function"
thumbnail: fimo-fractal-240.jpg
date: 2018-01-31
---

Most password hashes have a cost parameter that determines how long it takes to hash a password. As computers get faster over time, it is advisable to increase the cost parameter so that the password hash stays sufficiently slow.

<!-- photo source: https://www.flickr.com/photos/oskay/2089066344/in/photostream/ -->

## Hashing a hash

A common way to upgrade the hash algorithm is to use the new password hash on the output of the old hash algorithm. Let's say we previously hashed passwords using MD5, but now we want to switch to bcrypt. We change the password check function. It used to be this:

    function verify_password(input) {
        hash = read_hash_from_database()
        return hash == md5(input)
    }

And we change it to this:

    function verify_password(input) {
        hash = read_hash_from_database()
        return bcrypt_verify(md5(input), hash)
    }

As far as bcrypt is concerned, the MD5 is the password. This makes it possible to change the hashes in the database to our new format. We simply calculate a bcrypt hash over the old MD5 hash and store the new hash in our database.

## Client independent update

The ability to upgrade a hash without having the password is client independent update. If your hashes became obsolete because computers have been getting faster since you first developed your password mechanism, it could be desirable that you can increase the cost of the password hash so that it becomes slower. Some hash algorithms allow to do that without having the plaintext password, which is a property of the hash function called client independent update.

## Not possible for PBKDF2 

Imagine that we already use PBKDF2, but we want to increase the number of iterations. If you want to increase the number of iterations from 1000 to 5000, you just perform 4000 more iterations on the current hash to obtain the new hash. Unfortunately, this is not possible without having the plaintext password. PBKDF2 works like this:

    function pbkdf2(password, salt, iterations) {
        result = 0
        u = salt
        for i in 1..iterations {
            u = hmac(password, u)
            result = result ⊕ u
        }
        return result
    }

As you can see, it uses the plaintext password in every iteration. So we can't upgrade a PBKDF2 hash to a hash with a higher iteration count without the password.

## Yescrypt supports upgrading

Yescrypt looks like this:

    function yescrypt(password, salt, N, r, p, t, g, dkLen) {
        ...
        hash = password

        for i in 0..g {
            if i == g
                dklen_g = dkLen
            } else {
                dklen_g = 32
            }

            hash = yescrypt_kdf_body(hash, salt, N, r, p, t, dklen_g)

            N <<= 2
            t >>= 1
        }
        return hash
    }

The password is used in the first iteration, but after that the subsequent iterations no longer use it. This means that we can upgrade to a higher _g_ by performing the loop a couple of times on a password hash. For this to work, we must set use a hash length of 32 (dkLen=32). Otherwise the hash is truncated and we can no longer perform more iterations on our hash.

The way yescrypt implements client-independent updates is by calculating a hash over a hash. This is actually not that different from the first solution we discussed, at the start of this post. The only thing is that it is now implemented as part of the algoritm.

If we want to upgrade our hash, we loop up to our new value for _g_. We set _N_ and _t_ correctly, and start hashing as soon as we pass the old value for _g_:

    function upgrade(hash, salt, N, r, p, t, old_g, new_g) {
        for i in 0..old_g {
            N <<= 2
            t >>= 1
        }

        for i in old_g+1..new_g {
            hash = yescrypt_kdf_body(hash, salt, N, r, p, t, 32)

            N <<= 2
            t >>= 1
        }
        return hash
    }

Using this function, we can upgrade our existing hashes to hashes with a higher _g_ parameter, without knowledge of the password.

## Conclusion

Client independent update is a marginally useful feature to have in a password hashing function. It can be implemented in the application by simply hashing twice, so it is no big deal if an algorithm doesn't support it.
