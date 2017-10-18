


## Cats in superposition

Quantum computers work with quantum bits. Normal bits can only be one or zero, but quantum bits can also be in a _superposition_: both one and zero at the same time. This can be used to perform computations that are not possible in the non-quantum, classical world. In this post, we are particularly interested in how this can be used to break crypto. This is possible with quantum computing because it can perform some calculations dramatically faster than classical computers. Quantum computers are not faster at everything, but for some tasks the superposition can be used to perform calculations on all possible inputs simultanously. This is called quantum parallelism. I will explain it using cats in boxes.

Some scientists proposed that on a very small scale, particles can be in a superposition. Schrödinger made this more tangible by connecting the particle to a cat in a closed box. The particle and the cat are connected in some way, such that the cat is either alive or dead, depending on the particle. In a classical world, when you open the box and you see a dead cat, you know that the cat has been dead all along. However, in a quantum world, the cat is in a superposition. It is both alive and dead at the same time until you open the box. When you open the box and look inside, only then the universe determines the fate of the cat.

This seems strange to most people. This is because quantum particles act like nothing in the classical world. In fact, quantum particles don't act like cats in boxes. This also means that most of this article is factually incorrect, but it may still give you some feeling about how quantum calculations work.

## Cracking passwords with cats

Imagine we have a password protected laptop, and we want to crack the password. We could try every password until we get the correct one, but that would take a very long time. Instead, we may be able to use quantum to speed up the process.

We put the laptop along with the cat in the box. We instructed the cat beforehand to enter a random password. When we close the box, there is a superposition of cats that all try a different random password. In each parallel universe, the cat tries a different password. It is as if the cat in the box tries each password in parallel. However, as soon as we open the box, the state collapses and we end up with a classical situation. The cat tried only one password and it was probably the wrong one.

Now we do the same again, but we instruct the cat to enter a random password and climb out of the box if it is the correct one. We close the box, the cat is again in a superposition. However, this time the box opens and collapses to the state we want: the cat has entered the correct password. We obtained the password instantly, and in the classical world the password was just entered once. We used quantum parallelism to get lucky the first time.

## Increasing correct cat probability

Actually, it doesn't really work like that. We can't tell the cat with the correct password to come out of the box. Remember, as soon as we look in the box the quantum state collapses and we end up with just one cat that tried one password. The test whether we have the correct password is similar to looking in the box. We can't use if-statements when instructing the cats.

However, we can use quantum operations to increase the probability that the cat with the correct password comes out of the box when we open it. The price we pay for this is that the cat needs to enter multiple passwords, instead of just one.

Let's say the laptop has an eight-digit password consisting of just letters. If we want to try them all, we have to type in 26^8 ≈  2 × 10^11 passwords. The cat in the box only needs to try the square root of that number, 456976 passwords. This will still take a long time, but it is the difference between a week and 6000 years of cracking. When we open the box, we will have almost a 100% chance of finding the cat with the correct password in it.

## Speed and limitations

Our cat algorithm provides a quadratic speedup compared to classical cracking. If we want to try N things in the classical world, we only have to do sqrt(N) tries in the quantum computer. This cuts the "security bits" in half: a quantum computer can find a SHA512 hash (of 512 bits) as fast as a classical computer can calculate a SHA256 hash (of 256 bits). A common defense against this attack is thus to double the number of bits used in an algorithm.

We haven't yet said anything about how the laptop checks the password. The cracking cat algorithm makes no assumptions about that. This means that it works for cracking any password check. As we see later, we can crack some specific algorithms even faster. However, the password check we want to crack must be implemented as a quantum algorithm. If you want to reverse a MD5 hash, you first have to implement MD5 as a quantum algorithm. You can't give the cat a classical laptop, it has to be a quantum laptop that can be in a superstate just like the cat.

## Period finding

A Fourier transform splits a wave in separate frequencies. It is typically displayed on stereo amplifiers or audio players. It converts the wave into a histogram of which frequencies are present in the wave.

It is also possible to compute a Fourier transform over a function. This will show which "frequencies" are present in the function. In classical computers, we would first have to compute all outcomes of the function, and pass those to the Fourier transform. In quantum computers, however, we call the function once on a superposition. This in turn results in a superposition, which we can pass directly to the quantum Fourier transform. 

If we ask the cat in the box to compute one value, we will have just one value when we open the box. But instead we take all parallel cats and compute some property (the frequency) on all of them. When we open the box, there will be one frequency that occurs a lot in the function.

## Clock math

Clocks work modulo 12. Two hours after eleven o'clock is one o'clock, because 11 + 2 = 1 (mod 12). Now, let's say we want to find the period of f(x) = 9x (mod 12). The result of this function is 0, 9, 6, 3, 0, 9, 6, 3, 0, etc. As you can see it repeats after four times. This is the period (or order) of this function: 4. Our previously mentioned quantum Fourier algorithm can find that fast.

Also note that 4 is a divisor of 12. This means that 12 can be evenly divided by 4. This is a common property of the period, it evenly divides the modulus.

Suppose we have a very big clock of which we don't know the size. We would very much like to know the size of the clock. If we find the period of f(x) = 9x (mod ?) using our quantum Fourier function, we find out some period that evenly divides the size of the clock.

## How this works for RSA

In RSA, the modulus is published as part of the public key. Unlike the clock example, this is not equal to the order. The modulus is made by multiplying two primes, p and q, together. The order of the function is equal to (p-1)(q-1). We use our clock math algorithm to found some period that evenly divides (p-1)(q-1). From this we can calculate p and q, and thus the private key.

## Some math

We find the period r of the function f(x) = a^x mod N. This loops us around the ring ("clock") in r steps. This means that it loops around half the clock in r/2 steps. a^r/2 * a^r/2 = a^r = 1 mod N.
z = a^r/2. Replace a^r/2 by z: z * z = 1 mod N
z^2 = 1 mod N
z^2 - 1 = 0 mod N = N mod N
(z-1)(z+1) = 0 mod N = N mod N
p = z-1 (mod N), q = z+1 (mod N)

## Current state for quantum computers

## Conclusion

No cats were hurt in the making of this blog post.

* [Quantum algorithms: an overview](https://arxiv.org/pdf/1511.04206.pdf)
