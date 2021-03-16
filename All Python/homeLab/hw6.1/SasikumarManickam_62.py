# Written by Sasikumar Manickam
# Date  10/23/2017
# Assignment: Week 06.2
# Dice , craps game

import random

def craps() :

# open and read the file only once; use readline()
    
    while True:
        # throw pair of dices
        dice1 = random.randrange(1, 6)
        dice2 = random.randrange(1, 6)
        
        #print (dice1)
        #print(dice2)

        # find total
        
        total = dice1+dice2
        print (total)
        #
        if total == 7 or total == 11:
            print ('I win')
            return
        elif total == 2 or total == 3 or total == 12:
            print ('I loose')
            return
        #continue until either of above conditions 

#craps()
#################
# TEST 
#5
#9
#3
#I loose

#5
#7
#I win

#6
#8
#7
#I win


