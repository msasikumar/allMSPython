# Written by Sasikumar Manickam
# Date  11/07/2017
# Assignment: Week 08.4
# Recursions - Password recursion

def prompt():
    passw = input("Enter Password:")
    savpass = ''
    if len(passw) ==0:
       return prompt()
    else:
       savpass = passw
    return savpass

