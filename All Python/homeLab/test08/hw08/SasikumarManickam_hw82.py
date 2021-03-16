# Written by Sasikumar Manickam
# Date  11/07/2017
# Assignment: Week 08.2
# Recursions - Mix Strings
def alt (s, t):
    if len(s) == len(t) and len(s) > 0:
        print(s[0]+ t[0],end='')
        alt(s[1:len(s)], t[1:len(t)])
    else:
        return

#Tests
#alt("Hello","World")
#HWeolrllod
#alt("good","bye")

#alt("abc","123")
#a1b2c3

#alt("Hello","12345")
#H1e2l3l4o5
