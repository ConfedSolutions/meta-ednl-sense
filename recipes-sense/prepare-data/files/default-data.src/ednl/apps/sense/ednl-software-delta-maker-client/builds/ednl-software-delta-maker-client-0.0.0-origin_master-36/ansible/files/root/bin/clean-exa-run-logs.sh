#!/bin/bash

date

files=("smc" "smi" "smps" "smnd" "gmc")
location="/root/.exa-run/"
maximumsize=50000 #MB
for i in "${files[@]}"; do
	if [ -f "${location}${i}.log" ]; then
		actualsize=$(du "${location}${i}.log" | cut -f 1)
		if [ $actualsize -ge $maximumsize ]; then
			/usr/local/bin/exa-run cleanrestart $i
		fi
	fi
done
