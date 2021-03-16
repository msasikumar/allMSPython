# Written by Sasikumar Manickam
# Date  10/17/2017
# Assignment: Week 05.4
# find % of three letter words 

def numLetters():
    allCtr =0
    threeCtr=0
    while (True):
        word = input ("Enter Word:")
        if (word ==''):
            break
        if (len(word)==3):
            threeCtr += 1
        allCtr += 1 
    if (threeCtr > 0 and allCtr > 0 ):
        percentage = (threeCtr/allCtr ) * 100
        print ('Total words:',  allCtr)
        print ('Three letter words:', threeCtr)
        print ('It is {} % of three letter words'.format(percentage))

###TEST
##>>> numLetters()
##Enter Word:ABC
##Enter Word:cde
##Enter Word:abcd
##Enter Word:efgh
##Enter Word:
##Total words: 4
##Three letter words: 2
##It is 50.0 % of three letter words
##>>> 
