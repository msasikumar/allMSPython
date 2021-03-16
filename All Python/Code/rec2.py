#Recursion in python
def alt(s, t):
    if len(s) == len(t) and len(s) > 0:
        print(s[0]+t[0], end="")
        alt(s[1 : len(s)],t[1: len(t)])
    else:
        return   
    
alt('god', '123')