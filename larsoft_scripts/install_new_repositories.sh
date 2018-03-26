#!/bin/bash
source /cvmfs/dune.opensciencegrid.org/products/dune/setup_dune.sh
cd $2
path=$(echo "$1" |  cut -d '-' -f 1)
source $path/setup
setup git
setup gitflow
setup mrb
export MRB_PROJECT=larsoft
number=$(echo "$1" |  cut -d '-' -f 2)
mkdir larsoft_${number}
cd larsoft_${number}
mrb newDev -v ${number} -q e9:prof
p=$(pwd)
source ${p}/localProducts_larsoft_${number}_e9_prof/setup
cd srcs/
args=("$@")
ELEMENTS=${#args[@]}
for (( i=2;i<$ELEMENTS;i++)); do
    mrb g ${args[${i}]}
done

mrbsetenv
mrb i -j4
cd ../
mrbslp

