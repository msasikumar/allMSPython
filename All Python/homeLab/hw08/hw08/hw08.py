# Written by Sasikumar Manickam
# Date  11/07/2017
# Assignment: Week 08.1
# Recursions
def square(n):
    if n > 1000:
        print(n)
    else:
        square(n*n)
      
#TEST
#square(6)
#1296
#square(-6)
#1296
#square(6.1)
#1384.5840999999996
#square(10)
#10000
#square(1233)
#1233