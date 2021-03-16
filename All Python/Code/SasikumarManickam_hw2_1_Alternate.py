# Written by Sasikumar Manickam
# Date  09/19/2017
# Assignment Week 02.1 
# Validate password - Altermate version
# Program will loop until user enters valid password.

while True:
    validRank = 4
    #Accept password from user
    password  = input('Enter Password:')

    # The password must be at least 9 characters long
    passLen = len(password)
    if (passLen < 9):
         print (password ,'is less than 9 characters' )   
         validRank -= 1

    # The password may contain only alpha and numeric characters,
    # i.e. no spaces or special characters like $, ?, _, etc
    # created special charecters as much as i can see on the keyboard and a space

    specialcharectros = '!@#$%^&*()_+-={}[]:"<>,.?/~` '
    
    if password in specialcharectros:
         print (password ,'contains a non-alphanumeric character')
         validRank -= 1

    # The second character in the password must be a digit(0 – 9)
    secondLtr= password[1:2]
    if not (secondLtr >= '0' and secondLtr <= '9'):
         print ('The second character', secondLtr ,'is not a numeric')   
         validRank -= 1
   
    # The last character in the password must be an alpha character
    # (a – z, A – Z)

    # Find last charecter
    lastchar = password[-1]
    if not (lastchar >= 'A' and secondLtr <= 'z'):
         print ('The last character in the password must be an alpha character')   
         validRank -= 1
        
    if validRank==4:
         print ('Congratulations, password meets all the criteria')
         break
    else:
         print ('Sorry, Try again')
        
    
# Tests
# invlaid: pass, passwords, p7ass, p7word,p7ssword5, p@word, p7ss words
# valid: P7sswords, S4ashikumarm, t0olstechs, f4yoursafe


