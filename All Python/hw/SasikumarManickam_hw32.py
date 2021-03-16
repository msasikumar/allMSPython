# Written by Sasikumar Manickam
# Date  09/19/2017
# Assignment: Week 03.2
# Write a function findFib(n), that will find and return the nth Fibonacci number

def  findFib(n):
    a = 0
    b = 1
    for i in range(n):
        c = a+b
        # print(c)
        b=a
        a=c
    return c

def main():
    fibPos = eval(input("Enter the position of the desired number in the Fibonacci sequence:"))
    fibRes =findFib(fibPos)
    print ("The Fibonacci number in position", fibPos," is ",fibRes)

# Test Results

#Enter the position of the desired number in the Fibonacci sequence:6
#The Fibonacci number in position 6  is  8

#Enter the position of the desired number in the Fibonacci sequence:13
#The Fibonacci number in position 13  is  233


#Enter the position of the desired number in the Fibonacci sequence:25
#The Fibonacci number in position 25  is  75025

#main()