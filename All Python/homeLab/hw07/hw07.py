# Written by Sasikumar Manickam
# Date  10/31/2017
# Assignment: Week 07.1
# ATM Machine 


def main():
    result, acctData = startup('accounts.csv')
    #print(acctData)
    if not result:
        print ('Cannot get to the file')
        return

    #while True: ## Always on ATM. 
    userPIN = getUser(result, acctData)
    selection = 0 
    while selection != 4:
        ## get user menu option
        selection = menu(acctData[userPIN][0])
        # 1= Deposit
        if selection == 1:
            acctData[userPIN][2] = deposit(acctData[userPIN][2])
            print ('Your new balance is {0:.2f}'.format(acctData[userPIN][2]))
        # 2= Withdraw
        elif selection == 2:
            acctData[userPIN][2] = withdraw(acctData[userPIN][2])
            print ('Your new balance is {0:.2f}'.format(acctData[userPIN][2]))
        # 3= Check balance
        elif selection == 3:
            print ('Your current balance is {0:.2f}'.format(acctData[userPIN][2]))
        
        # 4= Quit
        elif selection == 4:
            print('Good bye')
        # otherwise, Invalid option
        else:
            print('Invalid option')

## Initialize session with file data
def startup(fileName):
    # initialize return values dict and result boolean
    accountDict = {}
    # open and read the file only once; use readline()
    try:
        file = open (fileName, 'r')
        lines = file.readlines()
        file.close()
    except:
        return (False, accountDict)

    # read throuugh and store it in a Dictionary
    for line in lines:
        acctData = line.split(",")
        
        # Layout:  key, [fName, lName, Balance]
        accountDict[acctData[0]] = [acctData[1]]
        accountDict[acctData[0]].append(acctData[2])
        accountDict[acctData[0]].append(float(acctData[3][:-1]))   ## convert balance to float
    return (True, accountDict) 

## Get user PIN 
def getUser(proceed, acctData):
    acctPIN = input('Welcome -- Enter your Pin: ')

    # verify the PIN in data dictionary
    if acctPIN in acctData.keys():
       return acctPIN 
    else:
       print ('Incorrect PIN')
       return(None, False)


# Deposit : Get amount and add to the current balance
def deposit(balance):
    balance += getamount()
    return balance

# Withdraw : Get amount and substract from the current balance
def withdraw(balance):
    while True:
        withdrawAmt  = getamount()
        if withdrawAmt < 0:
            print ('Amount cannot be negative')
        if balance < withdrawAmt:
            print ('Insufficient funds to complete the transaction')
        else:
            balance -= withdrawAmt
            return balance

# get the current balance
def getamount():
    goodamount = False
    
    ## loop until valid amount entered
    while not goodamount:
        try:
            userAmount = float(input('Enter Amount: '))
            goodamount = True
        except:
            print('You entered an incorrect amount. Please try again')
            goodAmount= False
    return userAmount


def menu(name):
    choice = 0
    while True:
        print('\n' * 60)
        print('{}:'.format(name))
        print('\n 1 : Deposit')
        print(' 2 : Withdrawal')
        print(' 3 : Check Balance')
        print(' 4 : Quit \n')
        try:
            choice = int(input('Enter Number:'))
            if choice not in [1,2,3,4]:
                print('Invalid choice')
            else:
                return choice
        except:
            print('Invalid choice')
        

# main()

### TEST
#Welcome -- Enter your Pin: 1234

#Hermoine:

# 1 : Deposit
# 2 : Withdrawal
# 3 : Check Balance
# 4 : Quit

#Enter Number:C
#Invalid choice

#Hermoine:

# 1 : Deposit
# 2 : Withdrawal
# 3 : Check Balance
# 4 : Quit

#Enter Number:3
#Your current balance is 23000.00

#Hermoine:

# 1 : Deposit
# 2 : Withdrawal
# 3 : Check Balance
# 4 : Quit

#Enter Number:2
#Enter Amount: hhh
#You entered an incorrect amount. Please try again
#Enter Amount: 32430-4-034
#You entered an incorrect amount. Please try again
#Enter Amount: 321323
#Insufficient funds to complete the transaction
#Enter Amount: 3000
#Your new balance is 20000.00

#Hermoine:

# 1 : Deposit
# 2 : Withdrawal
# 3 : Check Balance
# 4 : Quit

#Enter Number:2
#Enter Amount: 10000
#Your new balance is 10000.00

#Hermoine:

# 1 : Deposit
# 2 : Withdrawal
# 3 : Check Balance
# 4 : Quit

#Enter Number:1
#Enter Amount: hkhk
#You entered an incorrect amount. Please try again
#Enter Amount: r4rt-4
#You entered an incorrect amount. Please try again
#Enter Amount: 21323-
#You entered an incorrect amount. Please try again
#Enter Amount: 1000
#Your new balance is 11000.00

#Hermoine:

# 1 : Deposit
# 2 : Withdrawal
# 3 : Check Balance
# 4 : Quit

#Enter Number:3
#Your current balance is 11000.00

#Hermoine:

# 1 : Deposit
# 2 : Withdrawal
# 3 : Check Balance
# 4 : Quit

#Enter Number:4
#Good bye
#Press any key to continue . . .
