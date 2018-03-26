#!/bin/bash

cd /neutapps/
version=$(ls -t $path | head -1)
cd ${version}
path=$(pwd)

cd ~/larsoft/
source ${path}/setup
setup mrb
export MRB_PROJECT=larsoft
number=$(echo "$version" |  cut -d '-' -f 2)
mkdir larsoft_${number}
cd larsoft_${number}
mrb newDev -v ${number} -q e9:prof
p=$(pwd)
source ${p}/localProducts_larsoft_${number}_e9_prof/setup
cd srcs/
mrb g dunetpc
cd dunetpc/
mrbsetenv
mrb i -j4
cd ../
mrbslp
