def pattern(n):
     
    if n== 0:
       print('*',end='')
    else:
        print(n * '*')
        pattern(n-1)
pattern(3)