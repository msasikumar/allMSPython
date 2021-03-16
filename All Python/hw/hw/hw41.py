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
numLetters()
