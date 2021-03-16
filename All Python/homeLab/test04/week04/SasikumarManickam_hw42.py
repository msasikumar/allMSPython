# Written by Sasikumar Manickam
# Date  10/03/2017
# Assignment: Week 04.2
# Encrypt file


def processFile(fname, num):
 #  Open file in read only mode
    infile = open(fname, 'r')

 # Get the num streight
    if (num >= 26): 
       num =  num % 26
        
    if (num == 0 ):
        print ("No encryption processed because num shift is zero")
        return

    Alpha_SA= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
    Alpha_sa= 'abcdefghijklmnopqrstuvwxyz'

    Alpha_SA_Enc = Alpha_SA[num:26]+ Alpha_SA[0:num]
    Alpha_sa_Enc = Alpha_sa[num:26]+ Alpha_sa[0:num]

    print(Alpha_SA)
    print(Alpha_SA_Enc)
    
    print(Alpha_sa)
    print(Alpha_sa_Enc)
    
    infile = open(fname, 'r')
    outfilename =  fname[:-4] + '_ENC.txt'  # construct encryption file  name
   
    outfile = open(outfilename,'w')         # open file for output 

    linecount=0
    wordcount= 0
    charcount = 0
    # for each line in the file, check each word is a Week day.
    for line in infile:  
        linecount +=1
        word = line.split()
        wordcount += len(word)
        for i in word: 
            for char in i:
                charcount += 1
                if char in Alpha_SA:                # charector found in A set
                    ctrA = 0
                    for x in  Alpha_SA:             # Translate with A_ENC
                       ctrA +=1   
                       if char == x:                # write to out file 
                          enc_char =  Alpha_SA_Enc[ctrA-1:ctrA]
                          #print(char,'=' ,enc_char)
                          outfile.write(enc_char)
                          break;
                elif char in Alpha_sa:              # charector found in "a" set
                    ctrA = 0
                    for x in  Alpha_sa:             # Translate with A_ENC
                       ctrA +=1   
                       if char == x:  # write to out file 
                          enc_char =  Alpha_sa_Enc[ctrA-1:ctrA]
                          #print(char,'=' ,enc_char)
                          outfile.write(enc_char)
                          break;
                else:
                 #print(char)
                 outfile.write(char)
            outfile.write(' ')        # write a space for each word     
        outfile.write('\n')           # write a line break for each line  
       
    print('Statistics and encryption are complete')
    print ('The encrypted version of {} is {}'.format(fname, outfilename))
    print('Number Lines: {}'.format(linecount)) 
    print('Number Characters: {}'.format(charcount))
    print('Number Word: {}'.format(wordcount))

    infile.close()
    outfile.close()


fname = input("Enter File Name:")
num = eval(input("Enter cipher (0-26):"))
processFile(fname, num)

# processFile('1.txt', 1)
