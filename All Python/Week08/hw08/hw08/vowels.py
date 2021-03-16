def vowels(s):
    n = len(s)
    v = 'aeiou'
    counter = 0
    if n == 0:
        return 0
    elif s[n-1:n] in v: 
        #print(s[n-1:n], end = ' ' )
        counter = 1
    return vowels(s[:n-1])+ counter
#vowels('abed')