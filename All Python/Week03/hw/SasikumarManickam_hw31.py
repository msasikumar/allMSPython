# Written by Sasikumar Manickam
# Date  09/19/2017
# Assignment: Week 03.2

# Write a Python program that separates a list of at least 20 positive and negative integers 
# into a sorted list of even integers and a sorted list of odd integers.


def evenOdd (lst, EorO):
    resultLst = []
    for i in lst:
        if  EorO =='E':
            if (i%2 ==0):
                resultLst.append(i)
        elif EorO =='O':
              if (i%2 !=0):
                resultLst.append(i) 
    # Sort result 
    resultLst.sort() 
    return resultLst

def main():
   # lstNum =[-10, 10, -9 , 9 , -8, 8 , -7, 7, -6, 6, -5,5,  -4,4, -3,3, -2,2, -1, 1]
   # lstNum =[-200 ,-2, 10, 12 ,36 ,40 ,86 ,146, 198]
   # lstNum =[ -225, -63 ,-9 ,-7 ,1 ,7, 23, 39, 45, 55, 75, 95] 

    lstNum =[-200 ,-2, 10, 12 ,36 ,40 ,86 ,146, 198, -225, -63 ,-9 ,-7 ,1 ,7, 23, 39, 45, 55, 75, 95]
    EorO = input("What do you want displayed, the even (type E) or the odd (type O) numbers:")
    
    resultList= evenOdd(lstNum, EorO)
    
    # Check Result and display result message
    resultSize =  len(resultList) 

    # If No result, display why.
    if resultSize == 0:
        if  EorO =='E':
            print ('No Even Numbers in the List')
        elif EorO =='O':
            print ('No odd Numbers in the list')
        else:
            print ('Invalid type ', EorO)

    # print result list in 2 column format, if there is something in the result list
    else: 
        if resultSize%2 == 0:
            for n in range (0,resultSize,2):
                print ('{:4} {:4}'.format(resultList[n],resultList[n+1]))
        else:
            for n in range (0,resultSize-1,2):
                print ('{:4} {:4}'.format(resultList[n],resultList[n+1]))
            print ('{:4}'.format(resultList[-1]))

#Test Results
# lstNum =[-200 ,-2, 10, 12 ,36 ,40 ,86 ,146, 198]
#What do you want displayed, the even (type E) or the odd (type O) numbers:E
#-200   -2
#  10   12
#  36   40
#  86  146
# 198

#What do you want displayed, the even (type E) or the odd (type O) numbers:O
#No odd Numbers in the list

#lstNum =[ -225, -63 ,-9 ,-7 ,1 ,7, 23, 39, 45, 55, 75, 95] 
#What do you want displayed, the even (type E) or the odd (type O) numbers:O
#-225  -63
#  -9   -7
#   1    7
#  23   39
#  45   55
#  75   95
#What do you want displayed, the even (type E) or the odd (type O) numbers:E
#No Even Numbers in the List


# for  lstNum =[-200 ,-2, 10, 12 ,36 ,40 ,86 ,146, 198, -225, -63 ,-9 ,-7 ,1 ,7, 23, 39, 45, 55, 75, 95]
#What do you want displayed, the even (type E) or the odd (type O) numbers:E
#-200  -2
# 10   12
# 36   40
# 86  146
#198

#What do you want displayed, the even (type E) or the odd (type O) numbers:O
#-225  -63
#  -9   -7
#   1    7
#  23   39
#  45   55
#  75   95

# What do you want displayed, the even (type E) or the odd (type O) numbers:x
# Invalid type  x

#main()