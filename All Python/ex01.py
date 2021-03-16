def example1():
    age = eval(input("Enter Age"))
    if (age > 62):
        print ('Eligible')
    else :
        print ('Not Eligible')

def example2():
    composers = ['Raja','ARR', 'Dimm' ,'SIA' , 'Adle','Em']
    composer = input("Enter Composer")
    if (composers.count(composer) > 0):
       print ('Yes')
    else:
        print ('Nay')
     
def example21():
    composers = ['Raja','ARR', 'Dimm' ,'SIA' , 'Adle','Em']
    composer = input("Enter Composer")
    if composer in composers:
       print ('Yes')
    else:
        print ('Nay')

def example3():
    string ='Success is not final; failure is not fatal: It is the courage to continue that counts'
    print(string)
    search = input('Enter search string')
    ct = string.count(search)
    print (ct)
    
    if ( ct> 2):
        print ('yes')
    else:
        print ('no')    
        
        
    
