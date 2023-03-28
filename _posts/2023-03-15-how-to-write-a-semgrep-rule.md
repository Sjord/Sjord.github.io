---
layout: post
title: "How to write a semgrep rule"
thumbnail: writing-child-480.jpg
date: 2023-04-05
---


Semgrep is a tool to find certain code patterns. It parses each file into a syntax tree, and then performs your query on that tree. It is possible to pass queries on the command line, but you need a rule file if you want a more complex rule, or want to easily store and manage rules. These rule files are commonly written in YAML and have to contain specific fields, which makes it a little bit hard to get started with a rule. This post describes how to quickly write a semgrep rule.

<!-- photo source: https://pixabay.com/nl/photos/kind-spelen-studie-kleur-leren-865116/ -->

## YAML is not easy

[YAML](https://en.wikipedia.org/wiki/YAML) may look straightforward, but it [is](https://matrix.yaml.info/valid.html) [full](https://hitchdev.com/strictyaml/why/implicit-typing-removed/) [of](https://github.com/cblp/yaml-sucks) [pitfalls](https://www.arp242.net/yaml-config.html).

* Enable the YAML support or plugin for your editor.
* Use [block scalars](https://yaml-multiline.info/) with the `>` or `|` characters.
* It's possible to use normal JSON within YAML.
* It's possible to quote strings, either with single quotes or double quotes.

## Simple template

Here's a simple template for a semgrep rule, which finds calls to `os.system()` in Python files:

```
rules:
- id: kebab-case-identifier
  message: >-
    Message that is shown
    when this rule matches.
  severity: ERROR
  languages: [python]
  patterns:
    - pattern: |
        os.system(...)
```

## Taint mode template

```
rules:
- id: kebab-case-identifier
  mode: taint
  message: >-
    Message that is shown
    when this rule matches.
  severity: ERROR
  languages: [python]
  pattern-sources:
    - pattern: |
        request.get(...)
  pattern-sanitizers:
    - pattern: |
        int(...)
  pattern-sinks:
    - pattern: |
        os.system(...)
```


* Don't put dots in the identifier, but put rules in a directory structure.
* Use one rule per file.
* Use a YAML tool/extension.
* Jsonnet
* Write tests
