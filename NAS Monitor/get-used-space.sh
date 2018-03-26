#!/bin/bash
# Created by PhpStorm.
# Author: Theodoros Giannakopoulos (theodoros.giannakopoulos@cern.ch)
# Date: Date: 06/12/2016
OUTPUT="$(du -sh ../../../mnt/nas01/users/* > used-space.txt)"
echo "${OUTPUT}