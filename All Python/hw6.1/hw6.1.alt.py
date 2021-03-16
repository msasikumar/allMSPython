# Written by Sasikumar Manickam
# Date  10/23/2017
# Assignment: Week 06.1
# Line index list for keywords index index

def index (fname, letter) :

# open and read the file only once; use readline()
    file = open (fname, 'r')
    lines = file.readlines()
    file.close()

# Remove all punctuation from the text
# '",;.?:!


    table = str.maketrans('",;.?:!','       ')
    indexDict = {}

    lineCtr = 0
    for line in lines:
        lineCtr +=1
        line.translate(table)  
   
        words = line.split()
        for word in words:
            if word[0] == letter:
                if word in indexDict.keys() :
                    # print (lineCtr, end='')
                    # print (word)
                    #if not lineCtr in indexDict[word]:   # alternatively you can use set like indexDict[word] = set( indexDict[word])
                    indexDict[word].append(lineCtr)   
                    setLines = set( indexDict[word])
                    indexDict[word] =   list(setLines)  

                    # print(indexDict)
                else:
                #print (word)
                    indexDict[word]= [lineCtr]
    print (indexDict)
    

index('hw6.1.py','i')


