# Written by Sasikumar Manickam
# Date  09/19/2017
# Assignment Week 02.2 
# English Calcualtor


#Accept password from user

calcStr  = input('Enter Calcualtion:')

firstNumber = eval(calcStr[0])
lastNumber = eval(calcStr[-1])

if len(calcStr) == 4:
    symbol= calcStr[1:3]
   
if len(calcStr) == 3:
    symbol= calcStr[1:2]

# Validate for Div by Zero
if (symbol == '/' and lastNumber == 0):
    print ( 'Division by zero is not allowed')    

# if valid entry do the the calculation
else:
# Calcualte

    if symbol =='+':
        symbolWord ='plus'
        result = firstNumber + lastNumber
        
    if symbol =='-':
        symbolWord ='minus'
        result = firstNumber - lastNumber
    if symbol =='*':
        symbolWord ='multiply'    
        result = firstNumber * lastNumber    

    if symbol =='/':
        symbolWord ='divide'
        result = firstNumber / lastNumber

    if symbol =='**':
        symbolWord ='to the power'
        result = firstNumber ** lastNumber

    # Display Result
    numberwords=['Zero','One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine']

    print (numberwords[firstNumber], symbolWord, numberwords[lastNumber], 'is ', str(result))


# Tests
# 3+3 Three plus Three is  6
# 8-5 Eight minus Five is  3
# 5-5 = Five minus Five is  0
# 0*1 = Zero multiply One is  0
# 2*2 = Two multiply Two is  4
# 4/2 = Four divide Two is  2.0
# 2**2 = Two to the power Two is  4
