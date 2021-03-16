# Written by Sasikumar Manickam
# Date  11/07/2017
# Assignment: Week 08.4
# Recursions - Password recursion

def prompt():
    passw = input("Enter Password:")
    
    if len(passw) ==0:
       return prompt()
       savpass = ''
    else:
        savpass = passw
    return savpass
prompt()