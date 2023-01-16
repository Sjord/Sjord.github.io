---
layout: post
title: "String comparison timing attacks"
thumbnail: todo-480.jpg
date: 2023-01-18
---

## C

```c
#include "cpucycles.h"
#include <stdio.h>
#include <string.h>

int main(int argc, char ** argv) {
	long long start = 0;
	long long end = 0;
	char * secret = "hello world";
	int res = 0;
	cpucycles();
	start = cpucycles();
	for (int i = 0; i < 1e9; i++) {
		res = strcmp(secret, argv[1]);
	}
	end = cpucycles();
	printf("%d %lld\n", res, end - start);
}
```

This performs a billion string comparisons. I ran this program 100 times, a recorded the number of CPU cycles that elapsed while comparing strings. Results are as follows:

|         | Correct    | Incorrect  | Abs diff   | Pct diff |
|---------|------------|------------|------------|----------|
| average | 5680506186 | 5767798472 | 87292286   | 1.5%     | 
| minimum | 5440661710 | 5437918814 | -2742896   | -0.05%   |
| maximum | 9161311564 | 8731949264 | -429362300 | -5%      |
| median  | 5520967071 | 5522476374 | 1509303    | 0.03%    |

The average differs by 87292286 cycles for a billion comparisons, with 2.3 billion cycles per second. This comes out to a average difference of about 40 picoseconds per comparison.

Even after we did 100,000,000,000 comparisons, the numbers are not clearly better for the comparing the correct string. Even if the numbers were consistent, you would have to measure a tens of picosecond difference.

## C#

```c#
var secret = "hello world";

var res = 0;
var key = Environment.GetCommandLineArgs()[1];

for (var i = 0; i < 1e9; i++)
{
    res = secret.CompareTo(key);
}

Console.WriteLine(res);
```

The string comparison in C# is smarter than in C, because it takes the current culture into account when comparing Unicode.

Here, the difference is about 2-15 ns per comparison. However, the incorrect string takes consistently longer to compare than the correct string. This is the opposite from what we would expect when checking a character at a time, and breaking early when the character is different.

Comparing `haaaaaaaaaa` takes approximately 15 ns longer than `gaaaaaaaaaa`. So a timing attack would be possible if you could measure response time in the order of nanoseconds.

But comparing `heaaaaaaaaa` and `haaaaaaaaaa` take about the same time, even though the second letter now differs. There is much magic going on here, and our simple assumptions on character-by-character comparison is just plain wrong.

## Python

```python
import sys
import time

secret = 'hello world'

start_time = time.perf_counter_ns()
for i in range(100000000):
    res = secret == sys.argv[1]
end_time = time.perf_counter_ns()

print(res, end_time - start_time)
```

Difference is about 1 ns per comparison, per character.
