# Written by Sasikumar Manickam
# Date  09/19/2017
# Assignment: Week 03.3

# points) Write a function wordGame() that reads the text file, Pride_and_Prejudice.txt

import random

def wordgame():
    infile = open('Pride_and_Prejudice.txt', 'r')

    allLines= infile.readlines()
    infile.close()

    lineCount  = len(allLines)

    # get all words from the text file in an list
    allwords=[]
    for i in range (0,lineCount):
         for  w in allLines[i].split():
             allwords.append(w)

    w1 = random.choice (allwords)
    w2 = random.choice (allwords)

    w1ctr = allwords.count(w1)
    w2ctr = allwords.count(w2)

    print("Which word did the writer use more often, ", w1 , "or ", w2, "?")
    answer = input()

    if w1 == answer:
        if w1ctr > w2ctr:
            print ('You are Correct')
        else:
            print ('Sorry!')
           
    elif w2 == answer:
        if w2ctr > w1ctr:
            print ('You are Correct')
        else:
            print ('Sorry!')

    print(w1 ,'count', str(w1ctr))
    print(w2 ,'count', str(w2ctr))

#wordgame()
# Test Results
