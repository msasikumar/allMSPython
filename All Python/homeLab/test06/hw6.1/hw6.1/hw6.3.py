# Written by Sasikumar Manickam
# Date  10/23/2017
# Assignment: Week 06.3
# Student data entry

def studentData() :
  
# initialize 
    studentDict = {}
# loop until Enter pressed    
    while True:
        first = input ('Enter First Name :')
        if first =='':
            return studentDict

        last  = input ('Enter Last Name :')
        student = (first, last)
        # check student existis in the dict
        if student in studentDict.keys():
            print ('{} has {}'.format(student, studentDict[student]), end='')
        # ask 
            updateFlag  = input (' Update?')
            if updateFlag =='y' or updateFlag =='Y' :
        # update
                studentDict[student] = input ('Enter Student ID :')
        # or just add ID
        else:
                studentDict[student]  = input ('Enter Student ID :')

# first function to add/update data
def student():
    students = studentData()
# process retuned Dict
    for name in sorted(students.keys()):
        print ('{}, {} has student ID {} '.format(name[0] ,name[1], students[name]))
    
#student()
##################
## TEST 
#Enter First Name :s
#Enter Last Name :a
#Enter Student ID :1
#Enter First Name :s
#Enter Last Name :a
#('s', 'a') has 1 Update?y
#Enter Student ID :10
#Enter First Name :a
#Enter Last Name :b
#Enter Student ID :1
#Enter First Name :c
#Enter Last Name :d
#Enter Student ID :2
#Enter First Name :
#a, b has student ID 1
#c, d has student ID 2
#s, a has student ID 10