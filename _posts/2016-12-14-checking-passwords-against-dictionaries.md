
Users’ password choices are very predictable, so attackers are likely to guess passwords that have been successful in the past. These include dictionary words and passwords from previous breaches, such as the “Password1!” example above. For this reason, it is recommended that passwords chosen by users be compared against a “black list” of unacceptable passwords. This list should include passwords from previous breach corpuses, dictionary words, and specific words (such as the name of the service itself) that users are likely to choose. Since user choice of passwords will also be governed by a minimum length requirement, this dictionary need only include entries meeting that requirement.

When processing requests to establish and change memorized secrets, verifiers SHALL compare the prospective secrets against a list of known commonly-used, expected, and/or compromised values. For example, the list MAY include (but is not limited to):

*    Passwords obtained from previous breach corpuses
*    Dictionary words
*    Context specific words, such as the name of the service, the username, and derivates thereof

If the chosen secret is found in the list, the subscriber SHALL be advised that they need to select a different secret because their previous choice was commonly used, and be required to choose a different value.


Check username, service name


Convert all leet speek to characters before checking word

Check all dictionaries or only most-common passwords?

Cracking should take longer than 24h, with 100ms per password -> 1e6 passwords

Minimum lengte 8 -> check niet kortere wachtwoorden

accept passphrases (correct battery horse staple)

Zoek ook op voornamen
