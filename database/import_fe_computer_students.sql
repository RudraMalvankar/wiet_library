-- =====================================================
-- Import FE Computer Engineering Students (Batch 2025)
-- Total: 49 new students (SR 1 already exists)
-- Date: October 29, 2025
-- =====================================================

-- Start transaction for safe import
START TRANSACTION;

-- First, insert into Member table (required for foreign key)
INSERT INTO Member (MemberNo, MemberName, `Group`, AdmissionDate, ClosingDate, Status, BooksIssued) VALUES
-- SR 2
(2512, 'Arya Vijay Alegaonkar', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 3
(2513, 'Om Santosh Ambavale', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 4
(2514, 'Shlok Ajay Amberkar', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 5
(2515, 'Purva Sandeep Bahalkar', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 6
(2516, 'Swarnim Yogesh Bhaskar', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 7
(2517, 'Aashutosh Deepak Bhatnagar', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 8
(2518, 'Yashwant Chandrashekhar Bhosale', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 9
(2519, 'Prashwet Prashant Bhosle', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 10
(25110, 'Nimisha Sanjiv Borkar', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 11
(25111, 'Kirtesh Bhupendra Borole', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 12
(25112, 'Jyoti Subhash Chahar', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 13
(25113, 'Sanskruti Sambhaji Chame', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 14
(25114, 'Pradnyesh Pramod Chaudhari', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 15
(25115, 'Sumit Ramnath Chauhan', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 16
(25116, 'Deepesh Brijesh Chaurasia', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 17
(25117, 'Rajababu Vedavyas Chitturi', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 18
(25118, 'Anurag Omprakash Choubey', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 19
(25119, 'Shouvik Shantanu Chowdhury', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 20
(25120, 'Swaraj Sunil Deshmukh', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 21
(25121, 'Punithkumar Vishwanath Devadiga', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 22
(25122, 'Srushti Santosh Devrukhkar', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 23
(25123, 'Nishant Satyaprakash Dubey', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 24
(25124, 'Archita Devdas Ega', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 25
(25125, 'Aryan Babu Fulgeri', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 26
(25126, 'Shyam Ganesh Gaikwad', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 27
(25127, 'Gayatri Mahendra Gaikwad', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 28
(25128, 'Hiraman Apeksha Gaikwagd', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 29
(25129, 'Nirjara Roshan Gaonkar', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 30
(25130, 'Akshay Anil Ghag', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 31
(25131, 'Akshaya Subhash Ghanekar', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 32
(25132, 'Tanishka Dattatray Gole', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 33
(25133, 'Sakshi Ramesh Gupta', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 34
(25134, 'Ayush Rambabu Gupta', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 35
(25135, 'Jyoti Chhotu Gupta', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 36
(25136, 'Seema Dilip Gupta', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 37
(25137, 'Pandurang Arya Hadawale', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 38
(25138, 'Siddhi Ankush Harde', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 39
(25139, 'Purva Sanjay Hotkar', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 40
(25140, 'Rinkal Kailas Ishi', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 41
(25141, 'Bhushan Sanjay Jadhav', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 42
(25142, 'Vedashree Bhalchandra Jadhav', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 43
(25143, 'Harsh Vinod Jadhav', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 44
(25144, 'Aditya Santosh Jadhav', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 45
(25145, 'Dikshika Sanjay Jadhav', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 46
(25146, 'Anand Nageshwar Jaiswal', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 47
(25147, 'Rohit Harichandra Jaiswal', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 48
(25148, 'Pratik Prakash Jangale', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 49
(25149, 'Riddhi Pandurang Jogdand', 'Student', '2025-09-15', '2029-05-31', 'Active', 0),
-- SR 50
(25150, 'Arya Mahesh Joshi', 'Student', '2025-09-15', '2029-05-31', 'Active', 0);

-- Now insert into Student table with all details
INSERT INTO Student (
    MemberNo, Surname, MiddleName, FirstName, Email, Mobile, Gender, 
    Branch, CourseName, PRN, ValidTill, Address, CardColour, Password
) VALUES
-- SR 2
(2512, 'Alegaonkar', 'Vijay', 'Arya', 'aryaalegaonkar13@gmail.com', '9967252669', 'Female', 
 'Computer Engineering', 'Computer', 'C2512', '2029-05-31', 'E-3/41 Anupam Nagar Murbad Road Kalyan west', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 3
(2513, 'Ambavale', 'Santosh', 'Om', 'omambavale@gmail.com', '7715873035', 'Male', 
 'Computer Engineering', 'Computer', 'C2513', '2029-05-31', 'Room no.308, Shankar Parvati chs,Near Ashapura Mandir,Opp Bank of Baroda,Star Colony,Manpada Rd, Dombivli East', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 4
(2514, 'Amberkar', 'Ajay', 'Shlok', 'Shlokamberkar0807@gmail.com', '9920732067', 'Male', 
 'Computer Engineering', 'Computer', 'C2514', '2029-05-31', 'A2/004, Arjun Park , Tukaram nagar , Dombivli ( East )', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 5
(2515, 'Bahalkar', 'Sandeep', 'Purva', 'bahalkarpurva@gmail.com', '9321887278', 'Female', 
 'Computer Engineering', 'Computer', 'C2515', '2029-05-31', 'Room no 1 sugoy society chikhale baugh aagra road kalyan (w)', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 6
(2516, 'Bhaskar', 'Yogesh', 'Swarnim', 'swarnim2445@gmail.com', '8169687346', 'Male', 
 'Computer Engineering', 'Computer', 'C2516', '2029-05-31', 'Shree sadguru krupa sankul A 404 near jari Mari mandir tisgoan naka kalyan east', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 7
(2517, 'Bhatnagar', 'Deepak', 'Aashutosh', 'swatibhatnagar35@gmail.com', '9850493542', 'Male', 
 'Computer Engineering', 'Computer', 'C2517', '2029-05-31', 'Vasant Vihar garden C-1, 301,B-cabin road, navre nagar,Ambernath East', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 8
(2518, 'Bhosale', 'Chandrashekhar', 'Yashwant', 'Yashwantwill8@gmail.com', '8482839728', 'Male', 
 'Computer Engineering', 'Computer', 'C2518', '2029-05-31', '001,new shreeji dham , beside matrix hospital, katrap, badlapur east, badlapur', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 9
(2519, 'Bhosle', 'Prashant', 'Prashwet', 'prashwet.chess@gmail.com', '9987643447', 'Male', 
 'Computer Engineering', 'Computer', 'C2519', '2029-05-31', '501, Gurukrupa Tower, Bhoirwadi, Dombivli West', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 10
(25110, 'Borkar', 'Sanjiv', 'Nimisha', 'sanjivborkar104@gmail.com', '8108461279', 'Female', 
 'Computer Engineering', 'Computer', 'C25110', '2029-05-31', '9A/104, Anmol garden, Haji malang road , Pisvali, Kalyan (East) , district - Thane, 421306', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 11
(25111, 'BOROLE', 'BHUPENDRA', 'KIRTESH', 'borolekirtesh@gmail.com', '7400450196', 'Male', 
 'Computer Engineering', 'Computer', 'C25111', '2029-05-31', '404, Indra heritage, kopar gaon , Dombivli (W)', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 12
(25112, 'Chahar', 'Subhash', 'Jyoti', 'chaharj30@gmail.com', '8107651944', 'Female', 
 'Computer Engineering', 'Computer', 'C25112', '2029-05-31', 'B-18, Laxmi darshan , Thakurwadi, dombivli (west)', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 13
(25113, 'Chame', 'Sambhaji', 'Sanskruti', 'sanskrutichame81@gmail.com', '8591141780', 'Female', 
 'Computer Engineering', 'Computer', 'C25113', '2029-05-31', 'Shiv sai dham residensy, room no. 209 , wing A, phase -1, Nandivali , kalyan (East)', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 14
(25114, 'Chaudhari', 'Pramod', 'Pradnyesh', 'pradnyeshchaudhari2k8@gmail.com', '8850180789', 'Male', 
 'Computer Engineering', 'Computer', 'C25114', '2029-05-31', '9, Akshaydeep Chs, Behind telephone Exchange, Maharshtra nagar, Mahatma Phule Road, Dombivli (W)', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 15
(25115, 'Chauhan', 'Ramnath', 'Sumit', 'sc872479@gmail.com', '9892692265', 'Male', 
 'Computer Engineering', 'Computer', 'C25115', '2029-05-31', 'Shankar Yadav Chawl, Room no 2, Gr no 3, Hariyali village, Vikhroli E, Mumbai-400083', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 16
(25116, 'Chaurasia', 'Brijesh', 'Deepesh', 'deepuchaurasia22@gmail.com', '8356078415', 'Male', 
 'Computer Engineering', 'Computer', 'C25116', '2029-05-31', 'Kalyan', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 17
(25117, 'Chitturi', 'Vedavyas', 'Rajababu', 'karthikchitturi634@gmail.com', '9182796721', 'Male', 
 'Computer Engineering', 'Computer', 'C25117', '2029-05-31', 'Sangathan chowk buwapada ambernath west', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 18
(25118, 'Choubey', 'Omprakash', 'Anurag', 'anuragchoubeykdm@gmail.com', '9372367633', 'Male', 
 'Computer Engineering', 'Computer', 'C25118', '2029-05-31', '104,C-wing, Rajaram Plaza, Nandivali Talao Raod, Nandivali, Kalyan', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 19
(25119, 'Chowdhury', 'Shantanu', 'Shouvik', 'shouvikchowdhury149@outlook.com', '8551860053', 'Male', 
 'Computer Engineering', 'Computer', 'C25119', '2029-05-31', 'JP Harmony Pale road Ambernath East', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 20
(25120, 'Deshmukh', 'Sunil', 'Swaraj', 'swarajd1407@gmail.com', '07559298066', 'Male', 
 'Computer Engineering', 'Computer', 'C25120', '2029-05-31', 'Golden valley E wing  502 sonivli badlapur west', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 21
(25121, 'Devadiga', 'Vishwanath', 'Punithkumar', 'punithdevadiga696@gmail.com', '7304688344', 'Male', 
 'Computer Engineering', 'Computer', 'C25121', '2029-05-31', 'Mahatma gandhi nagar room No 1908, Kalyan(E)-421306', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 22
(25122, 'Devrukhkar', 'Santosh', 'Srushti', 'virendradevrukhkar48@gmail.com', '9769353679', 'Female', 
 'Computer Engineering', 'Computer', 'C25122', '2029-05-31', '501,Indira tower, opposite prakash hardware,manpada road, Dombivli east', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 23
(25123, 'Dubey', 'Satyaprakash', 'Nishant', 'dubeynishant434@gmail.com', '8108035452', 'Male', 
 'Computer Engineering', 'Computer', 'C25123', '2029-05-31', 'Satya sai krupa C.h.s kalish nagar kalyan (E)', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 24
(25124, 'Ega', 'Devdas', 'Archita', 'architaega@gmail.com', '8600336803', 'Female', 
 'Computer Engineering', 'Computer', 'C25124', '2029-05-31', 'Dhamankar naka road , padmanagar, bhiwandi', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 25
(25125, 'Fulgeri', 'Babu', 'Aryan', 'aryanfulgeri05@gmail.com', '7499640520', 'Male', 
 'Computer Engineering', 'Computer', 'C25125', '2029-05-31', 'Jadhav sundar Bai chawl nr kali mata Mandir road chinchpada Ambarnath west', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 26
(25126, 'Gaikwad', 'Ganesh', 'Shyam', 'shyam.g.gaikwad07@gmail.com', '9769488543', 'Male', 
 'Computer Engineering', 'Computer', 'C25126', '2029-05-31', '20/2nd floor Gopal Niwas Bldg Behind DNC school Dombivli East', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 27
(25127, 'Gaikwad', 'Mahendra', 'Gayatri', 'gaikwadgayatri579@gmail.com', '7208222036', 'Female', 
 'Computer Engineering', 'Computer', 'C25127', '2029-05-31', '204 jaishiv krupa co hou soc near gr patil school anand nagar badlapur east', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 28
(25128, 'Gaikwagd', 'Apeksha', 'Hiraman', 'anita.hiraman1404@gmail.com', '8898395726', 'Female', 
 'Computer Engineering', 'Computer', 'C25128', '2029-05-31', 'Bhagwan nagar katemanivali kalyan east', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 29
(25129, 'Gaonkar', 'Roshan', 'Nirjara', 'gaonkarnirjara567@gmail.com', '8652556555', 'Female', 
 'Computer Engineering', 'Computer', 'C25129', '2029-05-31', 'Dombivli East', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 30
(25130, 'Ghag', 'Anil', 'Akshay', 'akshaygh2007@gmail.com', '7208613055', 'Male', 
 'Computer Engineering', 'Computer', 'C25130', '2029-05-31', '108,Tower.24,Regency Anantam,Dombivli (East)', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 31
(25131, 'Ghanekar', 'Subhash', 'Akshaya', 'akshayaghanekar194@gmail.com', '8169172212', 'Female', 
 'Computer Engineering', 'Computer', 'C25131', '2029-05-31', 'Sai Krupa chawl no.2, behind kalubai appartment, Diva-Agasan road,Diva (E)', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 32
(25132, 'GOLE', 'DATTATRAY', 'TANISHKA', 'goletanishqa@gmail.com', '7972329692', 'Female', 
 'Computer Engineering', 'Computer', 'C25132', '2029-05-31', 'At post waluth tal jawoli dis satara', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 33
(25133, 'Gupta', 'Ramesh', 'Sakshi', 'sakshi.gupta050606@gmail.com', '9763332825', 'Female', 
 'Computer Engineering', 'Computer', 'C25133', '2029-05-31', 'Mohan tulsi vihar near Bharat college badlapur west', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 34
(25134, 'Gupta', 'Rambabu', 'Ayush', 'nilimagupta511@gmail.com', '9324433955', 'Male', 
 'Computer Engineering', 'Computer', 'C25134', '2029-05-31', 'Samrat Ashok Magar', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 35
(25135, 'Gupta', 'Chhotu', 'Jyoti', 'jyotigupta826782@gmail.com', '8432762678', 'Female', 
 'Computer Engineering', 'Computer', 'C25135', '2029-05-31', 'Neral pada, near Ice factory , Neral', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 36
(25136, 'Gupta', 'Dilip', 'Seema', 'Seem1402gupta@gmail.com', '9594288590', 'Female', 
 'Computer Engineering', 'Computer', 'C25136', '2029-05-31', 'Sant Tukaram chawl old vithal mandir kalyan East', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 37
(25137, 'Hadawale', 'Arya', 'Pandurang', 'aryahadawale246@gmail.com', '9867738297', 'Female', 
 'Computer Engineering', 'Computer', 'C25137', '2029-05-31', 'Omkar HSG Society, Parerawadi, Sakinaka, Ghatkopar (W) Mumbai-400072', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 38
(25138, 'Harde', 'Ankush', 'Siddhi', 'siddhiharde5@gmail.com', '9321545685', 'Female', 
 'Computer Engineering', 'Computer', 'C25138', '2029-05-31', 'Sagardeep, kashish park, Khadakpada, kalyan west', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 39
(25139, 'Hotkar', 'Sanjay', 'Purva', 'purvahotkar@gmail.com', '7558495621', 'Female', 
 'Computer Engineering', 'Computer', 'C25139', '2029-05-31', 'badlapur gaon , badlapur (w)', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 40
(25140, 'Ishi', 'Kailas', 'Rinkal', 'Princeishi19@gmail.com', '7776927416', 'Female', 
 'Computer Engineering', 'Computer', 'C25140', '2029-05-31', 'Som- shivam build no 9 mansarovar bhiwandi', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 41
(25141, 'Jadhav', 'Sanjay', 'Bhushan', 'jadhavbhushan210@gmail.com', '9372528351', 'Female', 
 'Computer Engineering', 'Computer', 'C25141', '2029-05-31', 'Raunak city sector 3 kalyan west', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 42
(25142, 'Jadhav', 'Bhalchandra', 'Vedashree', 'jadhavvedashree9a@gmail.com', '8329656560', 'Female', 
 'Computer Engineering', 'Computer', 'C25142', '2029-05-31', 'Sai prassna apt kurla camp Ulhasanagar 4', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 43
(25143, 'Jadhav', 'Vinod', 'Harsh', 'jharsh0529@gmail.com', '7709354660', 'Male', 
 'Computer Engineering', 'Computer', 'C25143', '2029-05-31', 'Suresh Jadhav chowk, Vadolgaon O.T. Section Ulhasnagar 3', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 44
(25144, 'Jadhav', 'Santosh', 'Aditya', 'adityajadhav0072106@gmail.com', '9833664350', 'Male', 
 'Computer Engineering', 'Computer', 'C25144', '2029-05-31', 'Anantibai Apt A 105 Nandivali Talav Road Nandivali Kalyan East', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 45
(25145, 'Jadhav', 'Sanjay', 'Dikshika', 'dikshikajadhav3992@gmail.com', '8369399287', 'Female', 
 'Computer Engineering', 'Computer', 'C25145', '2029-05-31', 'Vasant park ,A wing ,orchid,205  ,khadakpada ,Gandhar nagar,kalyan west', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 46
(25146, 'Jaiswal', 'Nageshwar', 'Anand', 'anandjaiswal1185@gmail.com', '8767749506', 'Male', 
 'Computer Engineering', 'Computer', 'C25146', '2029-05-31', 'Hanuman nagar midc , nagru kirana store ulhasnagar', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 47
(25147, 'Jaiswal', 'Harichandra', 'Rohit', 'rohitjaiwal078@gmail.com', '9137336124', 'Male', 
 'Computer Engineering', 'Computer', 'C25147', '2029-05-31', 'Sagaon manpada road dombivli east', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 48
(25148, 'Jangale', 'Prakash', 'Pratik', 'pratikjangale179@gmail.com', '9834161290', 'Male', 
 'Computer Engineering', 'Computer', 'C25148', '2029-05-31', 'Hendrepada, phase-3F 403 Mohan Tulsi vihar badlapur (W) 421503', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 49
(25149, 'Jogdand', 'Pandurang', 'Riddhi', 'jogdandriddhi@gmail.com', '9321529752', 'Female', 
 'Computer Engineering', 'Computer', 'C25149', '2029-05-31', 'Dombivli', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
-- SR 50
(25150, 'joshi', 'mahesh', 'arya', 'aryajoshi984@gmail.com', '7208585554', 'Male', 
 'Computer Engineering', 'Computer', 'C25150', '2029-05-31', '201 surekha bhavan old dombivli road near shankar mandir dombivli west 421202', 'Green', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Commit the transaction
COMMIT;

-- Verify the import
SELECT 
    COUNT(*) as TotalMembers,
    (SELECT COUNT(*) FROM Student) as TotalStudents,
    (SELECT COUNT(*) FROM Member WHERE `Group` = 'Student') as StudentMembers
FROM Member;

-- Show sample of imported students
SELECT 
    s.MemberNo,
    CONCAT(s.FirstName, ' ', s.MiddleName, ' ', s.Surname) as FullName,
    s.Email,
    s.PRN,
    s.Branch,
    m.Status
FROM Student s
JOIN Member m ON s.MemberNo = m.MemberNo
ORDER BY s.MemberNo
LIMIT 10;

-- =====================================================
-- Import Complete!
-- All FE Computer Engineering students added
-- Default password for all: 123456 (bcrypt hashed)
-- =====================================================
