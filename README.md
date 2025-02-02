Accounts available in the SQL

Admin -> admin@xyz.com      | P@ssw0rd!1
Faculty -> johnwee@xyz.com  | P@ssw0rd!2
Student -> meandyou@xyz.com | P@ssw0rd!3
_________________________________________________________
Students CRUD

CREATE: Under Navigation Bar, click Add Student to go to the add student page OR under Student
Records, click on the Add Students button. Ensure current logged-in user is either Faculty or 
Admin.

READ: Under Navigation Bar, click Student Records and Student Enrollment to view students
and their assigned courses. Display will be different for Students and Faculty and Admin

UPDATE: Under Navigation Bar, click Student Records and under the Actions tab click on Edit button 
to go to the edit student page. Ensure current logged-in user is either Faculty or Admin.

DELETE: Under Navigation Bar, click Student Records and under the Actions tab click on Delete
button on the student you wish to delete. Ensure current logged-in user is Admin.

_________________________________________________________
Course CRUD

CREATE: Under Navigation Bar, hover to Courses and click Create Course to go to the create course page.
Ensure current logged-in user is either Faculty or Admin, Student is unable to see this tab.

READ: Under Navigation Bar, hover to Courses and click Course Main to view all created courses.
Tab are only accessible to only Admin and Faculty.

UPDATE: Under Navigation Bar, hover to Courses and click Course Main to view all created courses.
Click the Edit buttons of selected courses to update course information.

DELETE: Under Navigation Bar, hover to Courses and click Course Main to view all created courses.
click the delete button of the courses that want to delete. note that only Admin roles can see this button.
_________________________________________________________
Class CRUD

CREATE: Under Navigation Bar, hover to Classes and click Modify Class. There are two options for creation. "Add New Class Name " and " Add New Class ". 
" Add New Class " allows for user to create a New class based off existing class names, class types and course codes.
" Add New Class Name " allows users to create new classnames.
These features are only available to Admin and Faculty.

READ: Under Navigation Bar, hover to Classes and click View Class.
Students are only able to see what classes they were enrolled in
Admin and Faculty are able to see all classes that were created.

UPDATE: Under Navigation Bar, hover to Classes and click Modify Class to view all created classes, under the Actions element, there is an edit table allowed for all classes that are not empty or dont have course codes. Just click on the button and user is able to edit and update unless there are duplicates.
Available for Admin and Faculty

DELETE: Under Navigation Bar, hover to Classes and click Modify Class to view all created classes. Under the Action element, there will be a delete button for that Entry of a class.
For empty classes, or courses with codes, there will be a
" Delete All Empty Classes " which essentially deletes all the class names that do not have any course codes or course names to them.
This is only available to Admin.
_________________________________________________________