# Written by Sasikumar Manickam
# Date  10/23/2017
# Assignment: Week 06.1
# Line index list for keywords 
import string

#print (string.punctuation)

def index (fname, letter) :  

# open and read the file only once; use readline()
    file = open (fname, 'r')
    lines = file.readlines()
    file.close()

# initialize 
    # table = str.maketrans('",;.?:!','       ')
    table = str.maketrans(string.punctuation, ' ' * len(string.punctuation))
    indexDict = {}
    lineCtr = 0
# process one line at a time
    for line in lines:
        lineCtr +=1
        # Remove all punctuation from the text
        line.translate(table)  
 # Process each word in line
        words = line.split()
        for word in words:
 # Check first letter of the word
            if word[0] == letter:
                if word in indexDict.keys() :
# if Line is not already in the list ..add..
                    if not lineCtr in indexDict[word]:   # alternatively you can use set; like set(indexDict[word])..
                        indexDict[word].append(lineCtr)   
                else:
                    indexDict[word]= [lineCtr]
 # display sorted 
    for key in sorted(indexDict.keys()):
        print ('{:10} :'.format(key), end='')
        for pagenumber in indexDict[key]:
            print (pagenumber,end=',')
        print()

#################
# TEST 
# index('hw6.1.py','i')
#if         :30,31,32,33,
#import     :5,
#in         :22,26,28,31,32,33,38,40,
#index      :4,9,
#index('hw6.1.py','i') :45,
#indexDict  :19,
#indexDict.keys() :31,
#indexDict[key]: :40,
#indexDict[word].append(lineCtr) :34,
#indexDict[word]: :33,
#indexDict[word]= :36,
#initialize :16,
#is         :32,
#Press any key to continue . . .

