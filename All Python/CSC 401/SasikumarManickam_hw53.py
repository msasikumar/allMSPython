
# Written by Sasikumar Manickam
# Date  10/17/2017
# Assignment: Week 05.3
# Repeat arithamtic ops until result reaches 1

def numLoops():
    num = eval(input('Enter Positive Number'))
    ctr = 0
    numSave = num
    while (num !=1):
        if (num % 2 == 1):
            num = num * 3 +1
        else:
            num = num //2   ## "//" is used for int result. we can use "/" also.
        ctr +=1
        print (num,end=' ')
    print ('\nIt takes {} time to reach 1 for the input {}'.format(ctr, numSave)) 

# TEST
#>>> numLoops()
#Enter Positive Number5
#16 8 4 2 1 
#It takes 5 time to reach 1 for the input 5
#>>> numLoops()
#Enter Positive Number7
#22 11 34 17 52 26 13 40 20 10 5 16 8 4 2 1 
#It takes 16 time to reach 1 for the input 7
#>>> numLoops()
#Enter Positive Number3
#10 5 16 8 4 2 1 
#It takes 7 time to reach 1 for the input 3
#>>> 
    

