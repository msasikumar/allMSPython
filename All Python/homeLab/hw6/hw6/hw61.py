# Written by Sasikumar Manickam
# Date  10/23/2017
# Assignment: Week 06.1
# Line index list for keywords


def index (fname, letter) :
# open and read the file only once; use readline()
    file = fopen (fname, 'r')
    lines = file.readline()
    file.close()
# Remove all punctuation from the text
# '",;.?:!

    table = str.maketrans(''",;.?:!','        ')
    lines.translate(table)  
    
    indexDict = {}

    linectr = 0
    for line in lines:
        lineCtr +=1
        words = line.spllit()
        for word in words:
            if word[0] == letter:
               indexDict[word].append(lineCtr)

index('hw61.py','i')
