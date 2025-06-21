-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 21, 2025 at 04:19 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_mmu_talent`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `posted_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `posted_by`, `created_at`, `image_path`) VALUES
(5, 'Welcome to MMU Got Talent!', 'Welcome, talents of MMU! We are thrilled to launch MMU Got Talent, a brand new platform dedicated to showcasing the incredible skills and creativity within our university. Create your profile, upload your portfolio, and connect with fellow students. Let\'s build a vibrant community together!', '17', '2025-06-15 02:00:00', 'announcement1.png'),
(6, 'New Feature: User Profiles & Talent Management', 'We have just rolled out a major update! You can now create and customize your personal profile page. Add your bio, upload a profile picture, and most importantly, add, edit, and delete your talents. Log in to your user dashboard to check it out!', '17', '2025-06-18 06:30:00', 'announcement2.png'),
(7, 'Now Live: The Talent Catalogue & Search', 'Finding talent has never been easier! The full Talent Catalogue is now live. You can browse all the amazing services offered by students, search for specific skills, and view detailed talent pages. The pagination feature ensures a smooth browsing experience.', '17', '2025-06-20 03:00:00', 'announcement3.png'),
(8, 'Introducing the Community Forum!', 'We are excited to announce the launch of our Community Forum. This is a place for you to connect with other students, ask questions, share ideas, and collaborate on projects. Head over to the Forum page and start a discussion today!', '17', '2025-06-21 10:00:00', 'announcement4.png');

-- --------------------------------------------------------

--
-- Table structure for table `faq`
--

CREATE TABLE `faq` (
  `id` int(11) NOT NULL,
  `question` text NOT NULL,
  `answer` text DEFAULT NULL,
  `submitted_by` varchar(100) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `visible` tinyint(1) DEFAULT 0,
  `type` enum('question','feedback') DEFAULT 'question',
  `flagged` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faq`
--

INSERT INTO `faq` (`id`, `question`, `answer`, `submitted_by`, `submitted_at`, `visible`, `type`, `flagged`) VALUES
(27, 'Your website needs abit of reworking in terms of design. ', 'Reworking is being done', 'crazydev2003@gmail.com', '2025-06-21 12:57:14', 1, 'question', 0),
(28, 'Are you guys going to add a chatbox with other talents to facilitate communication between other talents?', NULL, 'student2@gmail.com', '2025-06-21 14:08:47', 0, 'feedback', 1),
(29, 'How do I reset my password if I forget it?', 'Currently, you can contact an administrator to help you reset your password. We are working on an automated password reset feature that will be available soon.', 'student1@gmail.com', '2025-06-18 01:30:00', 1, 'question', 0),
(30, 'Is there a limit to the number of talents I can add to my profile?', 'No, there is no limit! You can add as many talents as you like to showcase all of your skills.', 'student2@gmail.com', '2025-06-18 03:00:00', 1, 'question', 0),
(31, 'Can I sell digital products like art commissions or music tracks?', 'Yes, absolutely. You can list any service or digital product. The platform currently simulates the transaction, and you will need to arrange the final delivery with the buyer directly.', 'student4@gmail.com', '2025-06-19 06:00:00', 1, 'question', 0),
(32, 'How are payments handled on the platform?', 'Currently, the shopping cart and checkout process are for simulation purposes only to demonstrate functionality. All actual payment and service delivery must be arranged directly between the buyer and the talent.', 'student7@gmail.com', '2025-06-19 08:20:00', 1, 'question', 0),
(33, 'Who can see my profile?', 'Your profile is visible to all users who are logged into the MMU Got Talent platform, including students, faculty, and potential collaborators.', 'student9@gmail.com', '2025-06-20 02:45:00', 1, 'question', 0),
(34, 'Are there plans to add a project collaboration feature?', NULL, 'student3@gmail.com', '2025-06-21 07:00:00', 0, 'question', 0),
(35, 'Can I link my social media profiles like LinkedIn or ArtStation to my user profile?', NULL, 'student5@gmail.com', '2025-06-21 09:10:00', 0, 'question', 0),
(36, 'Is there a mobile app for MMU Got Talent?', NULL, 'student8@gmail.com', '2025-06-21 10:30:00', 0, 'question', 0),
(37, 'The website is very user-friendly, but a dark mode would be a great addition for late-night browsing!', NULL, 'student6@gmail.com', '2025-06-20 14:00:00', 0, 'feedback', 1),
(38, 'It would be helpful if the talent catalogue had more advanced filtering options, like filtering by faculty or price range.', NULL, 'student10@gmail.com', '2025-06-21 00:00:00', 0, 'feedback', 0);

-- --------------------------------------------------------

--
-- Table structure for table `forum_posts`
--

CREATE TABLE `forum_posts` (
  `post_id` int(11) NOT NULL,
  `post_content` text NOT NULL,
  `post_date` datetime NOT NULL DEFAULT current_timestamp(),
  `topic_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forum_posts`
--

INSERT INTO `forum_posts` (`post_id`, `post_content`, `post_date`, `topic_id`, `user_id`) VALUES
(5, 'The new area in the library\'s extension is pretty good! Fewer people know about it.', '2025-06-20 10:25:00', 1, 45),
(6, 'I agree! Also, the tutorial rooms are usually empty after 6 PM. Just make sure the air-con is on.', '2025-06-20 10:30:00', 1, 48),
(7, 'Sometimes I just find an empty spot at the seating area in the Central Lecture Complex (CLC). It\'s surprisingly peaceful.', '2025-06-20 11:00:00', 1, 49),
(8, 'Has anyone tried the study rooms in the STC building? I heard they are good.', '2025-06-20 11:15:00', 1, 18),
(9, 'Yeah, the STC rooms are great but you might need to book them in advance during peak season.', '2025-06-20 11:45:00', 1, 50),
(10, 'Thanks for the suggestions everyone!', '2025-06-20 12:00:00', 1, 43),
(11, 'What kind of alternative rock do you guys play? Like Muse or more like Radiohead?', '2025-06-20 11:40:00', 2, 50),
(12, 'We cover bands like Arctic Monkeys, The Strokes, and some classic stuff too. We\'re open to new ideas!', '2025-06-20 11:55:00', 2, 44),
(13, 'My friend from FOE is a great guitarist. I\'ll pass him your contact.', '2025-06-20 12:10:00', 2, 50),
(14, 'Awesome, thanks! Let me know.', '2025-06-20 12:15:00', 2, 44),
(15, 'I play guitar but mostly acoustic. Not sure if I\'m good enough for lead.', '2025-06-20 13:00:00', 2, 49),
(16, 'We can always have a jam session and see how it goes! No pressure.', '2025-06-20 13:05:00', 2, 44),
(17, 'I love the AI ethics class! It\'s so relevant. The data visualization subject is also very practical.', '2025-06-20 14:10:00', 3, 52),
(18, 'I find the workload for the new subjects a bit heavy, especially the project components.', '2025-06-20 14:25:00', 3, 43),
(19, 'True, the workload is intense, but I feel like I\'m learning a lot more useful skills compared to the old curriculum.', '2025-06-20 15:00:00', 3, 47),
(20, 'Does anyone have the notes for the latest lecture? I missed it.', '2025-06-20 15:15:00', 3, 18),
(21, 'Check the Google Classroom, I think the lecturer uploaded the slides there.', '2025-06-20 15:30:00', 3, 52),
(22, 'Start early and don\'t procrastinate! That\'s the golden rule. Break down your project into small, manageable tasks.', '2025-06-21 09:20:00', 4, 47),
(23, 'Make sure you have regular meetings with your supervisor. Their guidance is crucial to stay on the right track.', '2025-06-21 09:35:00', 4, 43),
(24, 'I used the Pomodoro Technique to manage my time. 25 minutes of focused work, then a 5-minute break. It really helps!', '2025-06-21 10:00:00', 4, 45),
(25, 'And don\'t forget to take breaks and get enough sleep! Your health is important.', '2025-06-21 10:15:00', 4, 48),
(26, 'Thanks for the amazing advice, guys. I feel a bit more motivated now.', '2025-06-21 11:00:00', 4, 52),
(27, 'Also, document everything as you go. It will save you a lot of headaches when you write the final report.', '2025-06-21 11:30:00', 4, 18),
(28, 'Great point! Writing the report at the last minute is a nightmare.', '2025-06-21 11:45:00', 4, 43),
(29, 'Have you tried looking at the job boards in the Student Affairs Division (STAD)? They sometimes have listings.', '2025-06-21 11:35:00', 5, 48),
(30, 'There are a few cafes around Shaftsbury Square that are often looking for part-timers.', '2025-06-21 11:50:00', 5, 45),
(31, 'I do freelance graphic design. Maybe you can try online platforms like Upwork or Fiverr?', '2025-06-21 12:15:00', 5, 46),
(32, 'That\'s a good idea! How do you handle payments and clients on those platforms?', '2025-06-21 12:30:00', 5, 18),
(33, 'It takes some getting used to, but it\'s a great way to build a portfolio. I can share some tips if you want.', '2025-06-21 12:45:00', 5, 46),
(34, 'This is a great idea! Here\'s my ArtStation portfolio: [link]', '2025-06-21 16:00:00', 6, 46),
(35, 'Wow, your work is amazing! I love your character designs.', '2025-06-21 16:15:00', 6, 45),
(36, 'Thanks! I\'m still learning. Your use of color is fantastic!', '2025-06-21 16:30:00', 6, 46),
(37, 'I just posted my latest piece on Instagram. What do you guys think?', '2025-06-21 17:00:00', 6, 52),
(38, 'That looks incredible! The lighting is perfect.', '2025-06-21 17:15:00', 6, 18),
(39, 'We should do a collaboration sometime!', '2025-06-21 17:30:00', 6, 52),
(40, 'For me, nothing beats the chicken rice at the old canteen near the mosque. It\'s legendary.', '2025-06-22 12:15:00', 7, 43),
(41, 'I prefer the variety at the new FCM building. They have some decent Western food options.', '2025-06-22 12:30:00', 7, 49),
(42, 'Is the Nasi Lemak at the library cafe any good? I always see a long queue.', '2025-06-22 12:45:00', 7, 44),
(43, 'It\'s good but a bit pricey for a student budget, in my opinion.', '2025-06-22 13:00:00', 7, 18),
(44, 'The FOM canteen is the most balanced choice for me. Decent price and good food.', '2025-06-22 13:15:00', 7, 45),
(45, 'I second that. Their mixed rice is a lifesaver.', '2025-06-22 13:30:00', 7, 51),
(46, 'I\'m in for futsal! Count me in.', '2025-06-22 14:25:00', 8, 47),
(47, 'I\'d prefer badminton. When and where are you planning to play?', '2025-06-22 14:40:00', 8, 52),
(48, 'We can alternate between futsal and badminton. Maybe we can play at the new sports complex?', '2025-06-22 15:00:00', 8, 44),
(49, 'Sounds good! Create a WhatsApp group and add me.', '2025-06-22 15:15:00', 8, 47),
(50, 'Me too!', '2025-06-22 15:30:00', 8, 18),
(51, 'I agree, it\'s much better than the old system. The talent catalogue is very useful.', '2025-06-22 16:45:00', 9, 43),
(52, 'The mobile version is also quite responsive, which is a big plus.', '2025-06-22 17:00:00', 9, 47),
(53, 'I wish there was a dark mode option. The white background can be a bit bright at night.', '2025-06-22 17:15:00', 9, 52),
(54, 'That\'s a good suggestion! Maybe we can give that feedback to the developers.', '2025-06-22 17:30:00', 9, 18),
(55, 'The search function in the catalogue works really well.', '2025-06-22 17:45:00', 9, 44),
(56, 'What kind of mobile app is it? I\'m an FCI student with experience in Android development.', '2025-06-22 18:15:00', 10, 43),
(57, 'It\'s an app to help students find and share study materials. I have the full business plan ready. Can we meet up to discuss?', '2025-06-22 18:30:00', 10, 51),
(58, 'Sure, I\'m free tomorrow afternoon. Let me know the time and place.', '2025-06-22 18:45:00', 10, 43),
(59, 'Sounds interesting! Do you need a UI/UX designer as well? I might be able to help.', '2025-06-22 19:00:00', 10, 46),
(60, 'Definitely! A good design is crucial. Let\'s all meet up and discuss.', '2025-06-22 19:15:00', 10, 51),
(61, 'Great! Looking forward to it.', '2025-06-22 19:30:00', 10, 46);

-- --------------------------------------------------------

--
-- Table structure for table `forum_topics`
--

CREATE TABLE `forum_topics` (
  `topic_id` int(11) NOT NULL,
  `topic_subject` varchar(255) NOT NULL,
  `topic_content` text NOT NULL,
  `topic_date` datetime NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forum_topics`
--

INSERT INTO `forum_topics` (`topic_id`, `topic_subject`, `topic_content`, `topic_date`, `user_id`) VALUES
(1, 'Best places to study on campus?', 'Hey everyone, I find the library a bit too crowded sometimes. Any recommendations for quiet and comfortable study spots around the MMU Cyberjaya campus?', '2025-06-20 10:15:00', 43),
(2, 'Looking for a bandmate (Guitarist)', 'Our band needs a lead guitarist for a campus event next month. We mostly play alternative rock. Anyone interested or know someone good?', '2025-06-20 11:30:00', 44),
(3, 'Feedback on the new FCI curriculum', 'For all the FCI students, what are your thoughts on the new subjects added this trimester? I find the AI ethics class particularly interesting.', '2025-06-20 14:00:00', 47),
(4, 'Tips for managing final year project stress?', 'My FYP is starting to feel overwhelming. How did the seniors manage their time and stress levels? Any advice would be greatly appreciated!', '2025-06-21 09:05:00', 52),
(5, 'Any good part-time job opportunities?', 'Looking for a flexible part-time job near campus. Something related to graphic design or writing would be ideal. Any leads?', '2025-06-21 11:20:00', 46),
(6, 'Digital Art Showcase - Share Your Work!', 'This is a thread for all the digital artists out there! Post a link to your portfolio or share your latest artwork. Let\'s support each other!', '2025-06-21 15:45:00', 46),
(7, 'Let\'s talk about the best Canteens in MMU', 'Which canteen is the best in terms of food quality, price, and variety? My vote goes to the one near the FOM building.', '2025-06-22 12:00:00', 45),
(8, 'Futsal/Badminton group for weekends?', 'I want to organize a small group for futsal or badminton on weekends. Anyone interested in joining for some friendly matches?', '2025-06-22 14:10:00', 44),
(9, 'Review of \'MMU Got Talent\' website', 'What does everyone think of this new platform? I find it really easy to navigate. The user profile page looks great!', '2025-06-22 16:30:00', 51),
(10, 'Collaboration: Need a programmer for my startup idea', 'I have a business plan for a new mobile app but lack the technical skills to build it. Looking for a skilled FCI student to partner with.', '2025-06-22 18:00:00', 18);

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `service_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_title` varchar(255) NOT NULL,
  `service_description` text DEFAULT NULL,
  `service_image` varchar(255) DEFAULT NULL,
  `service_price` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `user_id`, `service_title`, `service_description`, `service_image`, `service_price`) VALUES
(15, 18, 'Assignment Helper', 'I can easily complete FCI-related assignments. My CGPA is 4.0, and I am in my final semester.', '6856ab423b5fc-download.jpeg', 50.00),
(16, 43, 'Custom Web App Development', 'Building responsive and dynamic web applications using PHP and JavaScript to meet your project needs.', 'images1.png', 150.00),
(17, 43, 'Database Design & SQL Tutoring', 'I can help design efficient database schemas and offer tutoring for complex SQL queries.', 'images2.png', 75.00),
(18, 43, 'API Integration Services', 'Expertise in integrating third-party APIs like payment gateways or social media logins into your website.', 'images3.png', 120.00),
(19, 44, '3D Model Prototyping', 'Creating detailed 3D models for mechanical parts and prototypes using CAD software.', 'images4.png', 200.00),
(20, 44, 'Arduino & IoT Projects', 'I can build and program custom IoT devices and automation systems using Arduino and Raspberry Pi.', 'images5.png', 180.00),
(21, 44, 'Robotics Tutoring (Mechanics)', 'Providing one-on-one tutoring on the mechanical principles of robotics and machine design.', 'images6.png', 80.00),
(22, 45, 'Social Media Marketing Strategy', 'Developing complete social media strategies to boost engagement and brand visibility.', 'images7.png', 100.00),
(23, 45, 'Market Research & Analysis', 'Conducting in-depth market research and providing actionable insights for your business idea.', 'images8.png', 130.00),
(24, 45, 'Content Writing for Ads', 'Crafting compelling copy for online advertisements and marketing campaigns.', 'images9.png', 60.00),
(25, 46, 'Digital Portrait Illustration', 'Creating unique and personalized digital portraits in various artistic styles.', 'images10.png', 90.00),
(26, 46, 'Logo & Brand Identity Design', 'Designing professional logos and complete branding packages for new businesses or projects.', 'images11.png', 250.00),
(27, 46, 'Custom T-Shirt Graphics', 'Designing eye-catching graphics for apparel and merchandise.', 'images12.png', 70.00),
(28, 47, 'Website Security Audit', 'Performing a basic security audit of your website to identify common vulnerabilities.', 'images13.png', 220.00),
(29, 47, 'Ethical Hacking Workshops', 'Conducting introductory workshops on the principles of ethical hacking and cybersecurity awareness.', 'images14.png', 150.00),
(30, 47, 'Network Security Consultation', 'Providing advice on how to secure your home or small office network.', 'images15.png', 100.00),
(31, 48, 'Public Speaking Coaching', 'Helping you build confidence and deliver powerful presentations for your classes or events.', 'images16.png', 85.00),
(32, 48, 'Blog & Article Writing', 'Writing engaging and SEO-friendly blog posts or articles on a variety of topics.', 'images17.png', 50.00),
(33, 48, 'Proofreading & Editing Services', 'Professional proofreading and editing for academic papers, resumes, and other documents.', 'images18.png', 40.00),
(34, 49, 'Short Film Video Editing', 'Professional video editing services for short films, vlogs, and promotional content.', 'images19.png', 180.00),
(35, 49, 'Color Grading Services', 'Enhancing your video footage with professional color grading to create the perfect mood.', 'images20.png', 110.00),
(36, 49, 'Intro & Outro Animation', 'Creating custom animated intros and outros for your YouTube channel or video projects.', 'images21.png', 95.00),
(37, 50, 'Circuit Design & Simulation', 'Designing and simulating basic electronic circuits for your engineering projects.', 'images22.png', 140.00),
(38, 50, 'Renewable Energy Tutoring', 'Tutoring sessions on solar and wind energy systems and their principles.', 'images23.png', 70.00),
(39, 50, 'PCB Layout Design', 'Creating custom PCB layouts for your electronic prototypes.', 'images24.png', 160.00),
(40, 51, 'Financial Modeling in Excel', 'Building custom financial models for business valuation, budgeting, and forecasting.', 'images25.png', 200.00),
(41, 51, 'Stock Market Analysis Tutoring', 'Introductory tutoring on fundamental and technical analysis for the stock market.', 'images26.png', 90.00),
(42, 51, 'Personal Budgeting Spreadsheets', 'Creating personalized Excel spreadsheets to help you manage your personal finances.', 'images27.png', 50.00),
(43, 52, 'Data Analysis with Python', 'Using Python (Pandas, NumPy) to analyze your datasets and extract valuable insights.', 'images28.png', 180.00),
(44, 52, 'Machine Learning Model Tutoring', 'Beginner-friendly tutoring on machine learning concepts and building simple models.', 'images29.png', 100.00),
(45, 52, 'Data Visualization Services', 'Creating beautiful and informative charts and dashboards from your data using tools like Matplotlib or Tableau.', 'images30.png', 120.00);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `buyer_user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `price_at_purchase` decimal(10,2) NOT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','completed') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `buyer_user_id`, `service_id`, `price_at_purchase`, `transaction_date`, `status`) VALUES
(65, 44, 16, 150.00, '2025-06-01 02:20:15', 'completed'),
(66, 45, 19, 200.00, '2025-06-01 03:45:30', 'completed'),
(67, 46, 22, 100.00, '2025-06-02 01:10:05', 'pending'),
(68, 47, 25, 90.00, '2025-06-02 06:05:22', 'completed'),
(69, 48, 28, 220.00, '2025-06-03 08:50:11', 'completed'),
(70, 49, 31, 85.00, '2025-06-03 10:20:45', 'completed'),
(71, 50, 34, 180.00, '2025-06-04 00:30:00', 'pending'),
(72, 51, 37, 140.00, '2025-06-04 04:12:12', 'completed'),
(73, 52, 40, 200.00, '2025-06-05 05:45:50', 'completed'),
(74, 18, 43, 180.00, '2025-06-05 07:00:00', 'completed'),
(75, 43, 20, 180.00, '2025-06-06 02:00:00', 'pending'),
(76, 45, 17, 75.00, '2025-06-06 03:10:00', 'completed'),
(77, 46, 21, 80.00, '2025-06-07 06:25:00', 'completed'),
(78, 47, 26, 250.00, '2025-06-07 07:30:00', 'completed'),
(79, 48, 29, 150.00, '2025-06-08 01:00:00', 'pending'),
(80, 49, 32, 50.00, '2025-06-08 03:45:00', 'completed'),
(81, 50, 35, 110.00, '2025-06-09 05:00:00', 'completed'),
(82, 51, 38, 70.00, '2025-06-09 08:10:00', 'completed'),
(83, 52, 41, 90.00, '2025-06-10 09:00:00', 'pending'),
(84, 18, 44, 100.00, '2025-06-10 10:05:00', 'completed'),
(85, 43, 21, 80.00, '2025-06-11 01:30:00', 'completed'),
(86, 44, 18, 120.00, '2025-06-11 02:45:00', 'completed'),
(87, 46, 24, 60.00, '2025-06-12 03:50:00', 'pending'),
(88, 47, 27, 70.00, '2025-06-12 04:30:00', 'completed'),
(89, 48, 30, 100.00, '2025-06-13 06:00:00', 'completed'),
(90, 49, 33, 40.00, '2025-06-13 07:15:00', 'pending'),
(91, 50, 36, 95.00, '2025-06-14 08:20:00', 'completed'),
(92, 51, 39, 160.00, '2025-06-14 09:40:00', 'completed'),
(93, 52, 42, 50.00, '2025-06-15 00:55:00', 'completed'),
(94, 18, 45, 120.00, '2025-06-15 02:10:10', 'pending'),
(95, 45, 16, 150.00, '2025-06-16 03:22:33', 'completed'),
(96, 44, 19, 200.00, '2025-06-16 04:34:56', 'completed'),
(97, 43, 22, 100.00, '2025-06-17 05:00:00', 'completed'),
(98, 48, 25, 90.00, '2025-06-17 06:45:00', 'pending'),
(99, 47, 28, 220.00, '2025-06-18 07:50:00', 'completed'),
(100, 46, 31, 85.00, '2025-06-18 08:00:00', 'completed'),
(101, 52, 34, 180.00, '2025-06-19 01:15:00', 'pending'),
(102, 51, 35, 110.00, '2025-06-19 02:30:00', 'completed'),
(103, 50, 40, 200.00, '2025-06-19 03:00:00', 'completed'),
(104, 49, 43, 180.00, '2025-06-20 04:00:00', 'completed'),
(105, 48, 17, 75.00, '2025-06-20 05:25:00', 'pending'),
(106, 47, 20, 180.00, '2025-06-20 06:40:00', 'completed'),
(107, 46, 21, 80.00, '2025-06-21 00:00:00', 'completed'),
(108, 45, 26, 250.00, '2025-06-21 01:10:00', 'completed'),
(109, 44, 29, 150.00, '2025-06-21 02:20:00', 'pending'),
(110, 43, 32, 50.00, '2025-06-21 03:30:00', 'completed'),
(111, 18, 35, 110.00, '2025-06-21 04:40:00', 'completed'),
(112, 52, 38, 70.00, '2025-06-21 05:50:00', 'completed'),
(113, 51, 41, 90.00, '2025-06-21 06:00:00', 'pending'),
(114, 50, 44, 100.00, '2025-06-21 07:10:00', 'completed'),
(115, 49, 18, 120.00, '2025-06-21 08:20:00', 'completed'),
(116, 48, 21, 80.00, '2025-06-21 09:30:00', 'completed'),
(117, 47, 24, 60.00, '2025-06-21 10:40:00', 'pending'),
(118, 46, 27, 70.00, '2025-06-21 11:50:00', 'completed'),
(119, 45, 30, 100.00, '2025-06-21 12:00:00', 'completed'),
(120, 44, 33, 40.00, '2025-06-21 13:10:00', 'pending'),
(121, 43, 36, 95.00, '2025-06-21 14:20:00', 'completed'),
(122, 18, 39, 160.00, '2025-06-21 14:30:00', 'completed'),
(123, 52, 42, 50.00, '2025-06-21 14:40:00', 'completed'),
(124, 51, 45, 120.00, '2025-06-21 14:50:00', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('student','admin') DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `student_id` varchar(50) DEFAULT NULL,
  `faculty` varchar(100) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `about_me` text DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT 'default_avatar.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `phone_number`, `password`, `role`, `created_at`, `student_id`, `faculty`, `date_of_birth`, `about_me`, `profile_picture`) VALUES
(17, 'Kalla Sharveswara Rao', 'Sharves@gmail.com', '011111111', '$2y$10$1wv6YTP0/3w6Ct5i99Zy/easMvVPt6qkLuWHbidRp4d07IQhhST0S', 'admin', '2025-06-21 11:27:36', NULL, NULL, NULL, NULL, 'default_avatar.png'),
(18, 'Kalla Deveshwara Rao', '1211103169@student.mmu.edu.my', '0142702915', '$2y$10$gb.Yi1t93.Z5PMo.ZtAebOM.vxU2LjIfwvhK087FXK883F9DnP6XG', 'student', '2025-06-21 12:50:14', '1211103169', 'FCI', '2003-06-05', 'Hello! I am Kalla Deveshwara Rao, and I am 22 years old. I have built several computer systems, have done a bit of spreadsheet data entry and manipulation, video and photo editing, dynamic website building (both front-end and back-end). I have also learnt multiple programming languages to broaden my scope. Currently, pursuing bachelor\'s degree in computer science, and not looking for work. Just trying to build a network. I have also worked as an internal auditor for a well-known aluminum extrusion company. I have done my fair share of event crew work in multiple events. I have developed android applications for my university projects.Hello! I am Kalla Deveshwara Rao, and I am 22 years old. I have built several computer systems, have done a bit of spreadsheet data entry and manipulation, video and photo editing, dynamic website building (both front-end and back-end). I have also learnt multiple programming languages to broaden my scope. Currently, pursuing bachelor\'s degree in computer science, and not looking for work. Just trying to build a network. I have also worked as an internal auditor for a well-known aluminum extrusion company. I have done my fair share of event crew work in multiple events. I have developed android applications for my university projects.', 'default_avatar.png'),
(41, 'Admin One', 'admin1@gmail.com', NULL, '$2y$10$gb.Yi1t93.Z5PMo.ZtAebOM.vxU2LjIfwvhK087FXK883F9DnP6XG', 'admin', '2025-06-21 13:05:45', NULL, NULL, NULL, NULL, 'default_avatar.png'),
(42, 'Admin Two', 'admin2@gmail.com', NULL, '$2y$10$gb.Yi1t93.Z5PMo.ZtAebOM.vxU2LjIfwvhK087FXK883F9DnP6XG', 'admin', '2025-06-21 13:05:45', NULL, NULL, NULL, NULL, 'default_avatar.png'),
(43, 'Alice Johnson', 'student1@gmail.com', '012-3456789', '$2y$10$gb.Yi1t93.Z5PMo.ZtAebOM.vxU2LjIfwvhK087FXK883F9DnP6XG', 'student', '2025-06-21 13:07:30', '1211100001', 'FCI', '2003-05-15', 'A passionate coder and problem solver, currently in my final year. I love building web applications and exploring new technologies.', 'student1.png'),
(44, 'Bob Williams', 'student2@gmail.com', '016-9876543', '$2y$10$gb.Yi1t93.Z5PMo.ZtAebOM.vxU2LjIfwvhK087FXK883F9DnP6XG', 'student', '2025-06-21 13:07:30', '1211100002', 'FOE', '2002-08-22', 'Mechanical engineering student with a keen interest in robotics and automation. Always looking for hands-on projects.', 'student2.png'),
(45, 'Charlie Brown', 'student3@gmail.com', '019-2345678', '$2y$10$gb.Yi1t93.Z5PMo.ZtAebOM.vxU2LjIfwvhK087FXK883F9DnP6XG', 'student', '2025-06-21 13:07:30', '1211100003', 'FOM', '2004-01-30', 'Business student specializing in marketing. I enjoy creating digital marketing campaigns and analyzing market trends.', 'student3.png'),
(46, 'Diana Miller', 'student4@gmail.com', '011-8765432', '$2y$10$gb.Yi1t93.Z5PMo.ZtAebOM.vxU2LjIfwvhK087FXK883F9DnP6XG', 'student', '2025-06-21 13:07:30', '1211100004', 'FCA', '2003-11-11', 'Creative artist with skills in digital illustration and graphic design. My passion is bringing ideas to life visually.', 'student4.png'),
(47, 'Ethan Davis', 'student5@gmail.com', '017-3456789', '$2y$10$gb.Yi1t93.Z5PMo.ZtAebOM.vxU2LjIfwvhK087FXK883F9DnP6XG', 'student', '2025-06-21 13:07:30', '1211100005', 'FCI', '2003-03-25', 'Cybersecurity enthusiast and ethical hacker. I am dedicated to making the digital world a safer place.', 'student5.png'),
(48, 'Fiona Wilson', 'student6@gmail.com', '013-1234567', '$2y$10$gb.Yi1t93.Z5PMo.ZtAebOM.vxU2LjIfwvhK087FXK883F9DnP6XG', 'student', '2025-06-21 13:07:30', '1211100006', 'FAC', '2004-07-07', 'Communications student who loves public speaking and content creation. I run a small blog in my free time.', 'student6.png'),
(49, 'George Taylor', 'student7@gmail.com', '014-7654321', '$2y$10$gb.Yi1t93.Z5PMo.ZtAebOM.vxU2LjIfwvhK087FXK883F9DnP6XG', 'student', '2025-06-21 13:07:30', '1211100007', 'FCM', '2002-09-18', 'Aspiring filmmaker and video editor. I have experience with short films, documentaries, and promotional videos.', 'student7.png'),
(50, 'Hannah Moore', 'student8@gmail.com', '018-2345678', '$2y$10$gb.Yi1t93.Z5PMo.ZtAebOM.vxU2LjIfwvhK087FXK883F9DnP6XG', 'student', '2025-06-21 13:07:30', '1211100008', 'FOE', '2003-12-01', 'Electrical engineering student fascinated by renewable energy systems. I am currently working on a solar panel efficiency project.', 'student8.png'),
(51, 'Ian Anderson', 'student9@gmail.com', '010-9876543', '$2y$10$gb.Yi1t93.Z5PMo.ZtAebOM.vxU2LjIfwvhK087FXK883F9DnP6XG', 'student', '2025-06-21 13:07:30', '1211100009', 'FOM', '2004-02-14', 'Finance major with strong analytical skills. Proficient in financial modeling and data analysis using Excel and Python.', 'student9.png'),
(52, 'Jane Thomas', 'student10@gmail.com', '012-8765432', '$2y$10$gb.Yi1t93.Z5PMo.ZtAebOM.vxU2LjIfwvhK087FXK883F9DnP6XG', 'student', '2025-06-21 13:07:30', '1211100010', 'FCI', '2003-06-30', 'Data science student with experience in machine learning and statistical analysis. I love finding stories in data.', 'student10.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faq`
--
ALTER TABLE `faq`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `topic_id` (`topic_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `forum_topics`
--
ALTER TABLE `forum_topics`
  ADD PRIMARY KEY (`topic_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `buyer_user_id` (`buyer_user_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `faq`
--
ALTER TABLE `faq`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `forum_posts`
--
ALTER TABLE `forum_posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `forum_topics`
--
ALTER TABLE `forum_topics`
  MODIFY `topic_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD CONSTRAINT `forum_posts_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `forum_topics` (`topic_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_posts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `forum_topics`
--
ALTER TABLE `forum_topics`
  ADD CONSTRAINT `forum_topics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`buyer_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
