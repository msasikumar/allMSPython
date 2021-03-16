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
#numLoops()      

