---
layout: post
title: "Grepping functions with ANTLR"
thumbnail: antlers-240.jpg
date: 2018-04-11
---

In order to find security bugs it is helpful to find specific patterns of code. In this post we will create a programming language parser to help us find certain methods.

<!-- photo source: https://www.pexels.com/photo/animal-antler-antlers-blur-219906/ -->

## Searching code

For security code reviews, I sometimes want to find a specific pattern in the code. To do this I typically use grep or some other text-based search tool, but this is often insufficient. Specifically if you want to find code that is missing, or to find some relation between several lines of code.

### Finding missing attributes

For example, in ASP.NET controller methods can be marked with attributes that specify certain behavior for the action. For example, that the method handles POST requests, that authentication is not needed, or that the method should be protected against CSRF:

        [HttpPost]
        [AllowAnonymous]
        [ValidateAntiForgeryToken]
        public IActionResult Login(string provider)
        {
            ...
        }

To check whether the application is consistently protected against CSRF, we want to find all methods with `[HttpPost]` and without `[ValidateAntiForgeryToken]`.

## Parsing the code

To solve this problem, we will parse the C# code. This way, we can reliably identify methods with their attributes and find our matching methods. We'll use the [ANTLR](http://www.antlr.org/) parser toolkit and its existing [grammars](https://github.com/antlr/grammars-v4). This takes much of the parsing process out of our hands. ANTLR creates code that can parse C# code, creates a *parse tree* and walks through this tree. What we have to do is implement a *listener* that performs some actions on specific nodes, with the goal to find the code we are interested in.

### Getting started with ANTLR

First, download [the ANTLR jar](https://github.com/antlr/antlr4/blob/master/doc/getting-started.md) and configure it so you have the commands `antlr4` and `grun` available. ANTLR is a tool that can convert grammars to parsing code. There already exists [grammars](https://github.com/antlr/grammars-v4) for many languages, including C#, which we'll use.

Convert the C# grammar to code:

    antlr4 grammars-v4/csharp/CSharpLexer.g4 grammars-v4/csharp/CSharpParser.g4

This will create a couple of Java files in the same directory as the grammar:

* CSharpLexer.java
* CSharpParser.java
* CSharpParserListener.java
* CSharpParserBaseListener.java

After we compile these Java files, we can use the grun tool from ANTLR to show us a parse tree.

    cd grammars-v4/csharp
    javac *.java
    grun CSharp compilation_unit -gui ~/somesource/AccountController.cs

<img src="/images/antlr-gui.png">

## Searching the parse tree

Initially I tried using [ANTLR's XPath support](https://github.com/antlr/antlr4/blob/master/doc/tree-matching.md#using-xpath-to-identify-parse-tree-node-sets) to find the nodes in the parse tree I am interested in. This won't work for two reasons:

* XPath is only partially implemented. It is possible to select nodes on type, but there is no support for expressions at all. So we can't use `//method[HttpPost]`, because the `[HttpPost]` expression is not supported.
* Using the parse tree directly doesn't give us the information we need. The parse tree is still a rather technical representation of the code. In our conceptial view, methods can have attributes. In the parse tree, class member declarations have both attributes and a method. To make it easier for ourselves, we should convert the parse tree to a more conceptual model.

### Tree listening

So we want to walk through the parse tree, make some model of which methods have which attributes and then print the methods we are interested in. The typical way to walk through the parse tree is to implement a *listener*. The listener has two methods for every type of node, which will be called when the walk enters or exits the node.

We create a listener by extending CSharpParserBaseListener:

    public class MyListener extends CSharpParserBaseListener {
        @Override public void enterClass_definition(CSharpParser.Class_definitionContext ctx) { 
            String className = ctx.identifier().getText();
            System.out.println("Entered class " + className);
        }
    }

In the main method we call the lexer, parser and run our listener over it:

    public class MyParser {
        public static void main(String[] args) throws IOException {
            CharStream input = CharStreams.fromFileName(args[0]);
            Lexer lexer = new CSharpLexer(input);
            TokenStream stream = new CommonTokenStream(lexer);
            CSharpParser parser = new CSharpParser(stream);
            ParseTree tree = parser.compilation_unit();
            MyListener listener = new MyListener();
            ParseTreeWalker.DEFAULT.walk(listener, tree);
        }
    }

Now we can run this on a C# file. It will parse the code and show all class names in the file:

    $ java MyParser ~/somesource/AccountController.cs
    Entered class AccountController

### Finding our methods

Now we know how to loop through the parse tree. We want to stop on attributes and methods, remember what belongs where and check if it matches our pattern when we are done with the method:

* A class member declaration contains both attributes and the method name. As soon as we see this, we start paying attention.
* When we encounter an attribute, we remember it for later.
* When we encounter a method, we remember its name.
* When we exit a class member declaration, we have both the attributes and the method name and we can check and print them.

In code, it looks like this:

    public class MyListener extends CSharpParserBaseListener {
        String currentClass = null;
        String currentMethod = null;
        List<String> attributes;
        boolean inClassMember = false;

        @Override public void enterClass_definition(CSharpParser.Class_definitionContext ctx) { 
            this.currentClass = ctx.identifier().getText();
        }

        // Class member declaration. This thing holds both the attributes and the method declaration.
        @Override public void enterClass_member_declaration(CSharpParser.Class_member_declarationContext ctx) { 
            this.attributes = new ArrayList<String>();
            this.inClassMember = true;
        }

        @Override public void enterAttribute(CSharpParser.AttributeContext ctx) { 
            if (this.inClassMember) {
                String attrName = ctx.namespace_or_type_name().identifier().get(0).getText();
                this.attributes.add(attrName);
            }
        }

        @Override public void enterMethod_declaration(CSharpParser.Method_declarationContext ctx) { 
            this.currentMethod = ctx.method_member_name().identifier().get(0).getText();
        }

        // In the exit we have collected our method name and attributes.
        @Override public void exitClass_member_declaration(CSharpParser.Class_member_declarationContext ctx) {
            if (this.attributes.contains("HttpPost") && !this.attributes.contains("ValidateAntiForgeryToken")) {
                System.out.println(this.currentClass + "." + this.currentMethod);
            }

            this.attributes = null;
            this.currentMethod = null;
            this.inClassMember = false;
        }   
    }

And this works great. It prints the methods with HttpPost attribute, and without ValidateAntiForgeryToken attribute.

## XPath support with JXPath

The code does what we want, but it does only what we want. We could make it a little bit more versatile. To do that, we create an abstract syntax tree, that we can query using XPath. For this example, we will limit us to a tree containing the class, which contains methods, which contain attributes. For this we create our own model classes that we construct in the parse tree listener. For example, when we enter a method declaration:

	@Override public void enterMethod_declaration(CSharpParser.Method_declarationContext ctx) { 
        String methodName = ctx.method_member_name().identifier().get(0).getText();
        MyMethod method = new MyMethod(methodName);
        this.currentMethod = method;
        this.currentClass.addMethod(method);
    }

After we have constructed the tree, we can use [JXPath](https://commons.apache.org/proper/commons-jxpath/) to perform queries on it. JXPath is a library that implements XPath for in-memory Java objects. When we exit the class, we pass the class to a JXPath context, perform our query on it and print all results:

	@Override public void exitClass_definition(CSharpParser.Class_definitionContext ctx) {
        Iterator results = JXPathContext.newContext(this.currentClass).iterate(this.xpath);
        while (results.hasNext()){
            System.out.println(results.next());
        }
    }   

This way, we can find the methods we are interested in using this XPath:

    //methods[attributes='HttpPost' and not(attributes='ValidateAntiForgeryToken')]

## Conclusion

It is pretty easy to construct a parser with ANTLR. Furthermore, JXPath makes it easy to query the resulting syntax tree. This is currently only a proof of concept, but I think it could be expanded relatively easy to support more types of queries in more programming languages. That would make a great tool to help with code reviews.

[GitHub repo](https://github.com/Sjord/funcgrep)
