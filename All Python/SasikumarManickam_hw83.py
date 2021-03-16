# Written by Sasikumar Manickam
# Date  11/07/2017
# Assignment: Week 08.3
# Recursions - stars
def stars(n):
    nlen = n
    if n==0:
        return 
    else:
        print ('*' * n)
        stars(n-1) 
    return print ('*' * n)

#### TESTS
##>>> stars(5)
##*****
##****
##***
##**
##*
##*
##**
##***
##****
##*****
##>>> stars(2)
##**
##*
##*
##**
##>>> stars(1)
##*
##*
##>>> stars(0)
##>>> 
