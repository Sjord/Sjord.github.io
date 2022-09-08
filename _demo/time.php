<?php
[$sec, $nsec] = hrtime();
printf("%d.%09d\n", $sec, $nsec);
