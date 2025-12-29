#!/bin/bash
appdir=/home/app/sense/${1}/builds/
builds=(`ls -rt ${appdir}`)
ignorelastbuilds=$((${#builds[@]} - 2))
removelogfiles=$(($ignorelastbuilds - 2))

i=0
while [ $i -lt $ignorelastbuilds ]; do
	if [ $i -lt $removelogfiles ]; then
		rm -rf ${appdir}${builds[$i]}
	else
		rm -rf ${appdir}${builds[$i]}/log/* 2> /dev/null 
		touch ${appdir}${builds[$i]}/log/cleaned
	fi
	let i++
done

appdir=/home/app/sense/${1}/download/
downloads=(`ls -rt ${appdir}`)
ignorelastdownloads=$((${#downloads[@]} - 5))

i=0
while [ $i -lt $ignorelastdownloads ]; do
	rm -rf ${appdir}${downloads[$i]}
	let i++
done

exit 0
