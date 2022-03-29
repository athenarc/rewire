import json
import os
import sys

original_stdout = sys.stdout

while (True):
    cmd = 'sudo batctl oj > origin.json'
    os.system(cmd)
    # Opening JSON file
    f = open('origin.json')

    # returns JSON object as
    # a dictionary
    data = json.load(f)

    #Closing file
    f.close()

    with open('bestoriginators.json', 'w') as f2:
        sys.stdout = f2
        #find how many best exist
        count = 0
        for i in data:
            for j in i:
                if j == 'best':
                    count += 1


        count2 = 0
        print("[", end='')
        # Iterating through the json
        # list
        for i in data:
            for j in i:
                if j == 'best':
                    print(i, end='')
                    count2 += 1
                    if count2 != count:
                        print(",", end='')
        print("]", end='')
        sys.stdout = original_stdout
        f2.close()
    cmd2 = 'cat bestoriginators.json | alfred -s 65'
    os.system(cmd2)

