// Done By Bradley
// Function to fetch attendance data
async function fetchAttendance() {
  try {
    const response = await fetch('attendance.json');
    if (!response.ok) {
      throw new Error('Failed to fetch attendance data');
    }
    const data = await response.json();
    console.log('Fetched attendance data:', data);
    return data;
  } catch (error) {
    console.error('Error loading attendance data:', error);
    showError('Failed to load attendance data. Please try again later.');
    return { attendance_records: [], attendance_summary: {} };
  }
}

// Function to render attendance records
function renderAttendance(data) {
  if (!data || !data.attendance_records || data.attendance_records.length === 0) {
    return '<p>No attendance records found.</p>';
  }

  // Group records by course
  const courses = {};
  data.attendance_records.forEach(record => {
    if (!courses[record.course_id]) {
      courses[record.course_id] = {
        name: record.course_name,
        records: []
      };
    }
    courses[record.course_id].records.push(record);
  });

  // Create HTML for each course's attendance
  return `
    <div class="attendance-container">
      <div class="attendance-summary">
        <h3>Attendance Summary</h3>
        <div class="summary-cards">
          <div class="summary-card present">
            <i class="fas fa-check-circle"></i>
            <div class="summary-info">
              <h3>${data.attendance_summary.present || 0}</h3>
              <p>Present</p>
            </div>
          </div>
          <div class="summary-card late">
            <i class="fas fa-clock"></i>
            <div class="summary-info">
              <h3>${data.attendance_summary.late || 0}</h3>
              <p>Late</p>
            </div>
          </div>
          <div class="summary-card absent">
            <i class="fas fa-times-circle"></i>
            <div class="summary-info">
              <h3>${data.attendance_summary.absent || 0}</h3>
              <p>Absent</p>
            </div>
          </div>
          <div class="summary-card total">
            <i class="fas fa-chart-line"></i>
            <div class="summary-info">
              <h3>${data.attendance_summary.attendance_rate || 0}%</h3>
              <p>Attendance Rate</p>
            </div>
          </div>
        </div>
      </div>

      ${Object.entries(courses).map(([courseId, course]) => `
        <div class="course-attendance">
          <h4>${course.name} (${courseId})</h4>
          <div class="attendance-table">
            <table>
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Status</th>
                  <th>Time In</th>
                  <th>Time Out</th>
                </tr>
              </thead>
              <tbody>
                ${course.records.map(record => `
                  <tr class="status-${record.status.toLowerCase()}">
                    <td>${record.date}</td>
                    <td><span class="status-badge">${record.status}</span></td>
                    <td>${record.time_in || '-'}</td>
                    <td>${record.time_out || '-'}</td>
                  </tr>
                `).join('')}
              </tbody>
            </table>
          </div>
        </div>
      `).join('')}
    </div>
  `;
}

// Function to show attendance
async function showAttendance() {
  showLoading('Loading attendance data...');
  try {
    const data = await fetchAttendance();
    const contentArea = document.getElementById('contentArea');
    if (contentArea) {
      contentArea.innerHTML = `
        <div class="attendance-header">
          <h3>Attendance Records</h3>
          <button id="exportAttendance" class="btn">
            <i class="fas fa-download"></i> Export
          </button>
        </div>
        ${renderAttendance(data)}
      `;

      // Add event listener for export button
      const exportBtn = document.getElementById('exportAttendance');
      if (exportBtn) {
        exportBtn.addEventListener('click', () => {
          alert('Exporting attendance data...');
          // Here you would typically implement the export functionality
        });
      }
    }
  } catch (error) {
    console.error('Error loading attendance:', error);
    showError('Failed to load attendance records. Please try again.');
  }
}

// Function to fetch courses from JSON
async function fetchCourses() {
  try {
    const response = await fetch('courses.json');
    if (!response.ok) {
      throw new Error('Failed to fetch courses');
    }
    const data = await response.json();
    console.log('Fetched courses:', data);
    return data;
  } catch (error) {
    console.error('Error loading courses:', error);
    showError('Failed to load courses. Please try again later.');
    return [];
  }
}

// Function to show error messages
function showError(message) {
  const contentArea = document.getElementById('contentArea');
  if (contentArea) {
    contentArea.innerHTML = `
      <div class="error-message">
        <i class="fas fa-exclamation-circle"></i>
        <p>${message}</p>
      </div>
    `;
  }
}

// Function to render courses in the dashboard
function renderCourses(courses) {
  if (!courses || courses.length === 0) {
    return '<p>No courses found.</p>';
  }
  
  return `
    <div class="courses-container">
      <h3>Your Courses</h3>
      <div class="course-cards">
        ${courses.map(course => `
          <div class="course-card">
            <h4>${course.course_id}: ${course.course_name}</h4>
            <p><strong>Instructor:</strong> ${course.instructor}</p>
            <p><strong>Interns:</strong> ${course.interns ? course.interns.join(', ') : 'None'}</p>
          </div>
        `).join('')}
      </div>
    </div>
  `;
}

// Function to show loading state
function showLoading(message = 'Loading...') {
  const contentArea = document.getElementById('contentArea');
  if (contentArea) {
    contentArea.innerHTML = `
      <div class="loading">
        <i class="fas fa-spinner fa-spin"></i>
        <p>${message}</p>
      </div>
    `;
  }
}

// Function to show default dashboard
function showDefaultDashboard() {
  const contentArea = document.getElementById('contentArea');
  if (contentArea) {
    contentArea.innerHTML = `
      <div class="dashboard-content">
        <h3>Dashboard Overview</h3>
        <div class="stats-container">
          <div class="stat-card">
            <div class="stat-icon">
              <i class="fas fa-book-open"></i>
            </div>
            <div class="stat-info">
              <h3>5</h3>
              <p>Active Courses</p>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon">
              <i class="fas fa-tasks"></i>
            </div>
            <div class="stat-info">
              <h3>12</h3>
              <p>Pending Submissions</p>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon">
              <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
              <h3>150</h3>
              <p>Total Students</p>
            </div>
          </div>
        </div>
        <div class="recent-activities">
          <h4>Recent Activities</h4>
          <p>No recent activities to display.</p>
        </div>
      </div>
    `;
  }
}

// Function to show courses
async function showCourses() {
  showLoading('Loading courses...');
  try {
    const courses = await fetchCourses();
    const contentArea = document.getElementById('contentArea');
    if (contentArea) {
      contentArea.innerHTML = `
        
        ${renderCourses(courses)}
      `;
    }
  } catch (error) {
    console.error('Error loading courses:', error);
    showError('Failed to load courses. Please try again.');
  }
}

// Function to show reports section
function showReports() {
  const contentArea = document.getElementById('contentArea');
  if (contentArea) {
    contentArea.innerHTML = `
      <h3>Reports Section</h3>
      <p>Submit or view your internship reports here.</p>
      <form id="reportForm">
        <label for="reportText">Weekly Report:</label><br>
        <textarea id="reportText" rows="5" style="width:100%;" placeholder="Write your report here..."></textarea><br><br>
        <button type="submit">Submit Report</button>
      </form>
    `;

    const form = document.getElementById('reportForm');
    if (form) {
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        const report = document.getElementById('reportText').value.trim();
        if (report) {
          alert('Report submitted successfully!');
          form.reset();
        } else {
          alert('Please enter your report before submitting.');
        }
      });
    }
  }
}

// Function to show supervisor info
function showSupervisorInfo() {
  const contentArea = document.getElementById('contentArea');
  if (contentArea) {
    contentArea.innerHTML = `
      <h3>Supervisor Info</h3>
      <p>Your assigned supervisor is <strong>Dr. Mensah</strong>.</p>
      <p>Email: <a href="mailto:dr.mensah@ashesi.edu.gh">dr.mensah@ashesi.edu.gh</a></p>
      <p>Office Hours: Tuesdays and Fridays at 4PM - 5PM</p>
    `;
  }
}

// Helper function to update active navigation
function updateActiveNav(activeLink) {
  document.querySelectorAll('.nav-links li').forEach(li => li.classList.remove('active'));
  if (activeLink && activeLink.parentElement) {
    activeLink.parentElement.classList.add('active');
  }
}

// Add styles for attendance
const attendanceStyles = `
  /* Attendance Styles */
  .attendance-container {
    padding: 20px 0;
  }

  .attendance-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
  }

  .summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0 30px;
  }

  .summary-card {
    background: white;
    border-radius: 8px;
    padding: 15px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  }

  .summary-card i {
    font-size: 2rem;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
  }

  .summary-card.present i { background-color: #4CAF50; }
  .summary-card.late i { background-color: #FFC107; }
  .summary-card.absent i { background-color: #F44336; }
  .summary-card.total i { background-color: #2196F3; }

  .summary-info h3 {
    margin: 0;
    font-size: 1.5rem;
    color: #2d3748;
  }

  .summary-info p {
    margin: 5px 0 0 0;
    color: #718096;
    font-size: 0.9rem;
  }

  .course-attendance {
    margin: 30px 0;
  }

  .course-attendance h4 {
    color: #2d3748;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e2e8f0;
  }

  .attendance-table {
    overflow-x: auto;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  }

  table {
    width: 100%;
    border-collapse: collapse;
  }

  th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
  }

  th {
    background-color: #f8fafc;
    font-weight: 600;
    color: #4a5568;
  }

  .status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
  }

  .status-present .status-badge {
    background-color: #e6ffed;
    color: #2f855a;
  }

  .status-late .status-badge {
    background-color: #fff8e1;
    color: #b76e00;
  }

  .status-absent .status-badge {
    background-color: #fff5f5;
    color: #c53030;
  }

  .btn {
    background-color: #4361ee;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: background-color 0.2s;
  }

  .btn:hover {
    background-color: #3a56d4;
  }
`;

// Initialize the dashboard
document.addEventListener('DOMContentLoaded', function() {
  // Check if already initialized
  if (window.dashboardInitialized) return;
  window.dashboardInitialized = true;
  
  console.log('Dashboard initialized');
  
  // Get DOM elements
  const contentArea = document.getElementById('contentArea');
  const welcomeText = document.querySelector('.welcome-banner h2');
  const homeLink = document.getElementById('homeLink');
  const coursesLink = document.getElementById('coursesLink');
  const reportsLink = document.getElementById('reportsLink');
  const attendanceLink = document.getElementById('attendanceLink');
  const supervisorLink = document.getElementById('supervisorLink');
  
  // Set welcome message
  const userName = document.getElementById('facultyName')?.textContent || 'Faculty';
  if (welcomeText) {
    welcomeText.innerHTML = `Welcome back, <span id="facultyName">${userName}</span>!`;
  }
  
  // Navigation click handlers
  function setupNavigation() {
    if (homeLink) {
      homeLink.onclick = function(e) {
        e.preventDefault();
        showDefaultDashboard();
        updateActiveNav(homeLink);
      };
    }
    
    if (coursesLink) {
      coursesLink.onclick = function(e) {
        e.preventDefault();
        showCourses();
        updateActiveNav(coursesLink);
      };
    }
    
    if (reportsLink) {
      reportsLink.onclick = function(e) {
        e.preventDefault();
        showReports();
        updateActiveNav(reportsLink);
      };
    }
    
    if (attendanceLink) {
      attendanceLink.onclick = function(e) {
        e.preventDefault();
        showAttendance();
        updateActiveNav(attendanceLink);
      };
    }
    
    if (supervisorLink) {
      supervisorLink.onclick = function(e) {
        e.preventDefault();
        showSupervisorInfo();
        updateActiveNav(supervisorLink);
      };
    }
  }
  
  // Initialize navigation and show default dashboard
  setupNavigation();
  showDefaultDashboard();
  if (homeLink) {
    updateActiveNav(homeLink);
  }
});

// Add styles for the dashboard
const style = document.createElement('style');
style.textContent = attendanceStyles + `
  /* Course cards */
  .courses-container {
    margin-top: 20px;
  }
  
  .course-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
  }
  
  .course-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    border-left: 4px solid #4361ee;
  }
  
  .course-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  }
  
  .course-card h4 {
    margin: 0 0 10px 0;
    color: #2d3748;
    font-size: 1.1rem;
  }
  
  .course-card p {
    margin: 8px 0;
    color: #4a5568;
    font-size: 0.95rem;
  }
  
  /* Loading state */
  .loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px;
    color: #4a5568;
  }
  
  .loading i {
    font-size: 2rem;
    margin-bottom: 15px;
    color: #4361ee;
  }
  
  /* Error message */
  .error-message {
    background: #fff5f5;
    border-left: 4px solid #e53e3e;
    color: #c53030;
    padding: 15px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  
  .error-message i {
    font-size: 1.2rem;
  }
  
  /* Stats cards */
  .stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0;
  }
  
  .stat-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 15px;
  }
  
  .stat-icon {
    font-size: 1.5rem;
    color: #4361ee;
  }
  
  .stat-info h3 {
    margin: 0;
    font-size: 1.5rem;
    color: #2d3748;
  }
  
  .stat-info p {
    margin: 5px 0 0 0;
    color: #718096;
    font-size: 0.9rem;
  }
  
  /* Navigation */
  .nav-links li.active {
    background-color: rgba(67, 97, 238, 0.1);
  }
  
  .nav-links li.active a {
    color: #4361ee;
  }
`;

document.head.appendChild(style);
