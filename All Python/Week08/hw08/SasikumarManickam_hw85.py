# Written by Sasikumar Manickam
# Date  11/07/2017
# Assignment: Week 08.2
# Recursions - Check numbers and print reverse order
def check (s):
    sl = len(s)
    if sl==0:
        return
    else:
        if s[sl-1] in (['0','1','2','3','4','5','6','7','8','9']):
            print (s[sl-1], end='')
    check(s[0:sl-1])

##### TEST
##>>> check('12abcd')
##21
##>>> check('dad12s3a4tsm5')
##54321
##>>> 
