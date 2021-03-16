def columnSum(lst):
    result=[]
    for i in range(len(lst)):
        sum=0
        for j in range (len(lst[i])):
            sum += lst[i][j]
        result.append(sum)
    #return result
    print (result)

#lst= [[1,2,4],[1,2,9]]
#res =columnSum(lst)
#print (res)
