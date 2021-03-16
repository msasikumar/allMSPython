# Written by Sasikumar Manickam
# Date  01/19/2019
# Assignment: Week 01

# Write python code to read a text file (you can use myfile.txt) and 
# output “word 1 True” if the word had already occurred in that file 
# before or “word 1 False” if this is the first time you encounter this word.


def isRepeated(fname):
    # Read file and store it in an array
    infile = open(fname, 'r')
    allLines= infile.readlines()
    infile.close()

    # get all words from the text file in a list
	lineCount  = len(allLines)
   
    allwords=[]
    for i in range (0,lineCount):
         for  w in allLines[i].split():
             allwords.append(w)
	
	# Sort words 	
	allwords.sort()
	prvWord=allwords[0]
	for word in allwords:
		if word == preWord
			outstring = 'Word {} True\n'.format(word )
		else
			prvWord=word
			outstring = 'Word {} False\n'.format(word )
		print (outstring)
    return

isRepeated ('test.txt')