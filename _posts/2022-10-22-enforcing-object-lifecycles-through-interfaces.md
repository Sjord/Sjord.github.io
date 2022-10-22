---
layout: post
title: "Enforcing object lifecycles through interfaces"
thumbnail: building-blocks-480.jpg
date: 2022-10-26
tags: programming
---

In object-oriented programming, objects be in a particular state in which it is not valid to call certain methods. This article explores a solution for when methods need to be called in a particular order.

<!-- photo source: https://pixabay.com/photos/child-tower-building-blocks-blocks-1864718/ -->

## Enforcing the order of method calls

For some classes, methods can only be called in a specific order. For example, when using TCP sockets, you first have to call `connect()` before you can call `send()`. This order is usually enforced at run-time, by throwing an exception when the methods are called out of order.

Consider this C# example:

```
using System.Net;
using System.Net.Sockets;
using System.Text;

var dest = IPEndPoint.Parse("127.0.0.1:4000");
var sender = new Socket(dest.AddressFamily, SocketType.Stream, ProtocolType.Tcp);
// sender.Connect(dest);
sender.Send(Encoding.ASCII.GetBytes("hello world"));
```

The call to `Connect()` has been commented out, and `Send()` complains about that"

```
Unhandled exception. System.Net.Sockets.SocketException (57): Socket is not connected
   at System.Net.Sockets.Socket.Send(Byte[] buffer)
   at Program.<Main>$(String[] args) in ConsoleApp4/Program.cs:line 8
```

This is enforced at run-time, but wouldn't it be cool if we could enforce it at compile time?

## Expose only callable methods in the interface

When we create a new socket, it should not have a `Send()` method on it, since we can't call it anyway. Only after we have called `Connect()` we should obtain an object that has a `Send()` method. Creating a new socket will give us a `IDisconnectedSocket`, which specifies a `Connect()` method that returns an `IConnectedSocket`. 

```
public interface IDisconnectedSocket
{
    public IConnectedSocket Connect(EndPoint remoteEP);
}

```

```
public interface IConnectedSocket
{
    public int Send(byte[] buffer);
}
```

We can only obtain a `IConnectedSocket` after calling `Connect()`, so it's no longer possible to call `Send()` without calling `Connect()` first.

These two interfaces can even be implemented on the same type. The `Socket` class could implement all methods, but methods are still protected against out-of-order execution because the caller does not have the interface to these methods. For example, the following class converts the normal `Socket` to one that conforms to our interfaces:

```
public class SocketAdapter : IConnectedSocket, IDisconnectedSocket
{
    private Socket socket;

    private SocketAdapter(Socket socket)
    {
        this.socket = socket;
    }
    
    public static IDisconnectedSocket Create(AddressFamily addressFamily, SocketType socketType, ProtocolType protocolType)
    {
        return new SocketAdapter(new Socket(addressFamily, socketType, protocolType));
    }
    
    public IConnectedSocket Connect(EndPoint remoteEP)
    {
        this.socket.Connect(remoteEP);
        return this;
    }
    
    public int Send(byte[] buffer)
    {
        return socket.Send(buffer);
    }
}
```

This class uses a private constructor and a `Create()` method here, so that it can return a `IDisconnectedSocket`. A constructor would return a `SocketAdapter`, and then someone could cal `Send()` on it.

## Conclusion

This pattern does not protect against all out-of-order method calls. For example, we could still call `Connect()` twice on a `IDisconnectedSocket`. However, I think it is a powerful pattern to catch mistakes at compile time, and it could make it easier to use the class correctly.
