# Written by Sasikumar Manickam
# Date  10/17/2017
# Assignment: Week 05.2
# find sum of inner list of a list and save it to a list

def columnSum(lst):
    result=[]
    for i in range(len(lst)):
        sum=0
        for j in range (len(lst[i])):
            sum += lst[i][j]
        result.append(sum)
    #return result
    print (result)

# TEST
##>>> columnSum ( [[5,9,2 ], [3,5,7 ], [ 8,1,6 ]] )
##[16, 15, 15]
##>>> columnSum ( [[1,15,15,4 ], [12 ,6,7,9], [23,89,34,55 ], [96,7,82,51 ]] )
##[35, 34, 201, 236]

