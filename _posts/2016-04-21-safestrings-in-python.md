
/proc/self/mem lezen werkt niet http://unix.stackexchange.com/questions/6301/how-do-i-read-from-proc-pid-mem-under-linux
os.abort() is makkelijkst
/proc/sys/kernel/core_pattern
    |/usr/share/apport/apport %p %s %c %P
    stopt crash in /var/crash/
    bae64 encoded, dus niet zo handig



sjoerd@ubuntu:~/dev/safestrings$ sudo bash
root@ubuntu:~/dev/safestrings# echo '%p.%e.core' > /proc/sys/kernel/core_pattern
root@ubuntu:~/dev/safestrings# exit
sjoerd@ubuntu:~/dev/safestrings$ ls
normal.py
sjoerd@ubuntu:~/dev/safestrings$ python normal.py 
Aborted

Geen core dumped?

ulimit -c unlimited

sjoerd@ubuntu:~/dev/safestrings$ grep helloworld 3720.python.core 
Binary file 3720.python.core matches


secret = 'helloworld'
secret = None

nog steeds er in

met gc.collect() nog steeds

Blijkt dat source code in de core zit. Lees key uit extern bestand

met secret = None nog steeds in geheugen
met gc.collect() nog steeds

libssl-dev, python-dev
pip install SecureString

SecureString.clearmem(secret)  # niet in geheugen!!! :)

Niet compatible met Python 3

Python string is niet meer immutable

memset werkt tegen: segfault bij clearen, met key nog in geheugen

Python heeft een string literal pool. Dus mutaten gaat kapot! String interning

Je wist één string, andere string gaat ook stuk



http://security.stackexchange.com/questions/74718/is-it-more-secure-to-overwrite-the-value-char-in-a-string
.NET heeft SecureString

Threat model:
    Heartbleed (remote memory reading)
    Memory scraping malware
    Een ander proces krijgt een stuk memory van een proces die net een wachtwoord heeft opgeslagen, op Windows 95
    Geheugen wordt geswapped en je verkoopt je harde schijf, of hibernation
    Cache/branch prediction side channel attacks
    Hardware met DMA, e.g. Firewire, Thunderbolt
    Cold-boot attack

http://security.stackexchange.com/questions/29019/are-passwords-stored-in-memory-safe
