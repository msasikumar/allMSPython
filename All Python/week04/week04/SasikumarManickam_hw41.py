# Written by Sasikumar Manickam
# Date  10/02/2017
# Assignment: Week 04.1

# Write a function (fname) that counts how often each weekday is mentioned 
# in a text file with name fname, and writes the results to a
# text file with name NumberOfDays.txt


def getCount(fname):
    # Initialize Week days and counts list
    Weekdays = ['Sunday','Monday','Tuesday','Wednesday','Thursday', 'Friday','Saturday']
    WeekdaysCount =[0,0,0,0,0,0,0]
    outFileName = 'NumberOfDays.txt'

    #  Open file in read only mode
    infile = open(fname, 'r')
    
    # for each line in the file, check each word is a Week day.
    for line in infile:  
        word = line.split()
        for i in range(len(word)):                      # find which day it is
                if word[i] in Weekdays:                 # Check if week day exists 
                    for j in range(len(Weekdays)):
                        if Weekdays[j] ==  word[i] :
                            #print(Weekdays[j], word[i])
                            WeekdaysCount[j] += 1       # Increment by 1
    #print (WeekdaysCount)
    infile.close()

    # Open file to Write.
    outfile = open(outFileName, 'w')
    
    # Write lines from Weekdays list and result list
    for x in range(len(Weekdays)):
        outstring = '{} is mentioned {} times in {} \n'.format(Weekdays[x],WeekdaysCount[x], fname )
        print (outstring)
        outfile.write(outstring)
   
    outfile.close()
    return

#getCount ('Pride_and_Prejudice.txt')
