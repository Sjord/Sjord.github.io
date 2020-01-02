---
layout: post
title: "Grepping functions with srcML"
thumbnail: fern-480.jpg
date: 2020-01-29
---

In a previous post, [Grepping functions with ANTLR](/2018/04/11/grepping-functions-with-antlr/), we looked into parsing source code to perform queries on it using XPath. For that post, I implemented a custom parser using ANTLR. In this post we look at an alternative: [srcML](https://www.srcml.org/) is a software project that converts source code to XML so we can query it using XPath.

<!-- photo source: https://pixabay.com/photos/fern-brake-plant-green-leaf-801784/ -->

### Recap

Like described in the [previous post](/2018/04/11/grepping-functions-with-antlr/), we want to find functions that have the `HttpPost` attribute, but not the `ValidateAntiForgeryToken` attribute, in order to find actions that are vulnerable to CSRF. Normal searching tools such as grep don't work here, since you can't search for an attribute that is not there. Therefore, we want to parse the source code and query it.

### About srcML

[SrcML](https://www.srcml.org/) converts source code to XML. It supports C, C++, C#, and Java. The XML it generates is largely independent of the language. Functions in C are converted to a `<function>` tag, and methods in Java are also converted to a `<function>` tag. That makes it easier to use queries across languages without having to learn a new object model.

### Using srcML

To test srcML, I used a random C# project from GitHub, [ASCOM.NETStandard](https://github.com/alvahdean/ASCOM.NETStandard). First, we tell srcML to convert these files to XML:

    srcml ~/dev/ASCOM.NETStandard -o ascom.xml

This creates a 15 MB XML file, which contains the syntax tree for all given files. Here is part of the XML file that corresponds to [this function](https://github.com/alvahdean/ASCOM.NETStandard/blob/master/WebService/ASCOM.WebService/Controllers/Ascom/UI/DomeStateController.cs#L31-L46):

    <comment type="line">// POST: DomeState/Create</comment>
    <function><attribute>[<expr><name>HttpPost</name></expr>]</attribute><attribute>[<expr><name>ValidateAntiForgeryToken</name></expr>]</attribute><specifier>public</specifier><type><name>ActionResult</name></type><name>Create</name><parameter_list>(<parameter><decl><type><name>IFormCollection</name></type><name>collection</name></decl></parameter>)</parameter_list><block>{
        <try>try
        <block>{
            <comment type="line">// TODO: Add insert logic here</comment>

            <return>return <expr><call><name>RedirectToAction</name><argument_list>(<argument><expr><call><name>nameof</name><argument_list>(<argument><expr><name>Index</name></expr></argument>)</argument_list></call></expr></argument>)</argument_list></call></expr>;</return>
        }</block>
        <catch>catch
        <block>{
            <return>return <expr><call><name>View</name><argument_list>()</argument_list></call></expr>;</return>
        }</block></catch></try>
    }</block></function>

### Querying using xmllint

Now that we have an XML representation, we can use any XPath tool to query it. One tool that is often installed and can do XPath queries is xmllint. Because our document has a namespace, we have to use the shell function of xmllint to specify the namespace, and remember to add a prefix to every tag. The following command queries the syntax tree:

    xmllint --shell ascom.xml
    / > setns src=http://www.srcML.org/srcML/src
    / > xpath //src:function[src:attribute//src:name='HttpPost' and not(src:attribute//src:name='ValidateAntiForgeryToken')]/src:name/text()
    Object is a Node Set :
    Set contains 36 nodes:
    1  TEXT
        content=SetSerialTrace
    2  TEXT
        content=SetSerialTraceFile
    3  TEXT
        content=SetState
    ...

This returns a list of 36 function names that have the `HttpPost` attribute, but not the `ValidateAntiForgeryToken` attribute.

### Conclusion

SrcML offers a XML tree specification and a tool to convert source code to XML, which makes it possible to query source code using XPath.


### Read more

* [Semmle QL](https://github.com/Semmle/ql)
* [AST Path for Python](https://github.com/hchasestevens/astpath/)
* [Grasp for JavaScript](https://www.graspjs.com/)
* [Gogrep for Go](https://github.com/mvdan/gogrep)
* [AST Grep for JavaScript](https://github.com/azz/ast-grep)
* [ESQuery for ECMAScript](https://github.com/estools/esquery)
