#!/usr/bin/env php 
<?php

for ($i = 1; $i <= 100; $i++) {

	print("\t$i\t: ");
	if ($i % 3 == 0) {
		print("triple");
	}
	if ($i % 5 == 0) {
		print("fiver");
	}
	print("\n");
}
