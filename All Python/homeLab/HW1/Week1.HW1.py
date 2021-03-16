# Written by Sasikumar Manickam
# Date  09/12/2017
# Assignment Week 01.1 
# Find Sum of best 2 of 3 entered numeric values.

#Accept 3 input data from user
s1  = eval(input('Enter 1st Homework Scrore:'))
s2  = eval(input('Enter 2nd Homework Scrore:'))
s3  = eval(input('Enter 3rd Homework Scrore:'))

# Sum of all 3 - lowest value gives Sum of best 2.

SumofBesttwo = s1+s2+s3 - min(s1,s2,s3)
# Print Result
print ('The average homework score for the class= ', SumofBesttwo);
print ('*Lowest score', min(s1,s2,s3), 'ignored')

#Error 
if (SumofBesttwo <= 100 and SumofBesttwo >= 0)
    print 'You messed up! Maximum can be 100 and cannot be negative')

## End ##

