# Written by Sasikumar Manickam
# Date  09/12/2017
# Assignment Week 01.2 
# Calculates a vehicle’s average gas mileage.

#Accept 3 input data from user
vType   = input('Enter Vehicle Type           :')
tSize   = eval(input('Enter Tank size (in gallons) :'))
fMilage = eval(input('Enter Mileage for full tank  :'))

# Miles per Gallon (MPG)  = full tank mialge(m)/Tank size (g)

MPG  = fMilage/tSize

# Print Result
print ('The ', vType, 'gets', MPG, ' miles per gallon') 
## End ##
