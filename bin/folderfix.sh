#!/bin/bash

if [ "$(uname)" == "Darwin" ]
then
	# Mac OSX
        sed -i '' "s@map\: \.@map\: $PWD@g" Homestead.yaml
elif [ "$(expr substr $(uname -s) 1 5)" == "Linux" ]
then
	sed -i "s@map\: \.@map\: $PWD@g" Homestead.yaml
elif [ -n "$COMSPEC" -a -x "$COMSPEC" ]
then 
	var=$PWD 
	sub=${var:1:1} 
	workdir=${var/$sub/":/"};
	sed -i "s@map\: \.@map\: $workdir@g" Homestead.yaml
fi
