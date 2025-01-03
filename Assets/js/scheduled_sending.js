
// console.log('Scheduled Sending JS loaded');

//     document.addEventListener('DOMContentLoaded', function () {
//     	setupMenuRefresh();
//         initCustomEmailSchedules();
//     });


// function scheduledSendingDetails() {
//     console.log('111111Scheduled Sending JS initialized');
//     setupMenuRefresh();
//     initCustomEmailSchedules();
// }


// function initCustomEmailSchedules() {
//     console.log('Initializing Scheduled Sending scripts');
//     console.log('Checking elements:', {
//         addRow: document.getElementById('addRow'),
//         saveButton: document.getElementById('saveButton'),
//         sendNow: document.getElementById('sendNow'),
//         emailList: document.getElementById('emailList'),
//     });

//     loadData();
//     loadSentData();
//     setupListeners();
// }


// document.addEventListener('majax:success', function () {
//     console.log('majax:success event detected');
//     if (window.location.pathname === '/s/scheduledsending') {
//         console.log('Reinitializing Scheduled Sending scripts after PJAX load');
//         initCustomEmailSchedules();
//     }
// });

// document.addEventListener('DOMContentLoaded', function () {
//     console.log('DOM fully loaded');
//     setupMenuRefresh();
//     initCustomEmailSchedules();
// });



// function setupMenuRefresh() {
//     const scheduledSendingMenu = document.querySelector('a[href="/s/scheduledsending"]'); // Replace with the actual selector for your menu item.

//     if (scheduledSendingMenu) {
//         scheduledSendingMenu.addEventListener('click', function (event) {
//             event.preventDefault(); // Prevent PJAX or default behavior
//             window.location.href = '/s/scheduledsending'; // Redirect and reload the page
//         });
//     }
// }
// function setupListeners() {
//     const container = document.querySelector('.custom-email-schedule');
//     if (!container) {
//         console.error('Container not found for event delegation.');
//         return;
//     }

//     container.addEventListener('click', function (event) {
//         if (event.target.id === 'addRow') addNewRow();
//         if (event.target.id === 'saveButton') saveData();
//         if (event.target.id === 'sendNow') sendNow();
//     });
// }


//     function addNewRow() {
//         const ul = document.getElementById('emailList');
//         const li = document.createElement('li');

//         const numberInput = document.createElement('input');
//         numberInput.type = 'number';
//         numberInput.value = '500';
//         numberInput.min = '1';
//         numberInput.step = '1';
//         numberInput.style.width = '50px';

//         const deleteButton = document.createElement('button');
//         deleteButton.textContent = 'Delete';
//         deleteButton.addEventListener('click', () => li.remove());

//         li.appendChild(numberInput);
//         li.appendChild(deleteButton);
//         ul.appendChild(li);
//     }

// function loadData() {
//     fetch('/s/scheduledsending/load-schedules')
//         .then(response => {
//             if (!response.ok) {
//                 throw new Error('Network response was not ok');
//             }
//             return response.json();
//         })
//         .then(data => {
//             if (data.success) {
//                 const ul = document.getElementById('emailList');
//                 ul.innerHTML = ''; // Clear existing list items
//                 data.data.forEach(number => {
//                     const li = document.createElement('li');
//                     const numberInput = document.createElement('input');
//                     numberInput.type = 'number';
//                     numberInput.value = number;
//                     numberInput.min = '1';
//                     numberInput.step = '1';
//                     numberInput.style.width = '50px';

//                     const deleteButton = document.createElement('button');
//                     deleteButton.textContent = 'Delete';
//                     deleteButton.addEventListener('click', () => li.remove());

//                     li.appendChild(numberInput);
//                     li.appendChild(deleteButton);
//                     ul.appendChild(li);
//                 });
//             } else {
//                 console.error('Failed to load schedules:', data.error);
//                 alert('Failed to load schedules. Please try again.');
//             }
//         })
//         .catch(error => console.error('Error fetching schedules:', error));
// }



// function saveData() {
//     const schedules = Array.from(document.querySelectorAll('#emailList li input[type="number"]'))
//         .map(input => parseInt(input.value, 10))
//         .filter(value => !isNaN(value));

//     if (schedules.length === 0) {
//         if (!confirm("You are about to save an empty schedule. Continue?")) {
//             return; // If the user cancels, don't proceed
//         }
//     }

//     fetch('/s/scheduledsending/save-schedules', {
//         method: 'POST',
//         headers: { 'Content-Type': 'application/json' },
//         body: JSON.stringify({ schedules }),
//     })
//         .then(response => {
//             if (!response.ok) {
//                 throw new Error('Network response was not ok');
//             }
//             return response.json();
//         })
//         .then(data => {
//             if (data.success) {
//                 alert('Schedules saved successfully.');
//             } else {
//                 alert('Error saving schedules: ' + (data.error || 'Unknown error'));
//             }
//         })
//         .catch(error => {
//             console.error('Error saving schedules:', error);
//             alert('An error occurred while saving schedules.');
//         });
// }

// function loadSentData() {
//     fetch('/s/scheduledsending/load-sent-schedules')
//         .then((response) => response.json())
//         .then((data) => {
//             if (data.success && Array.isArray(data.data)) {
//                 const ul = document.getElementById('pastEmailsList');
//                 ul.innerHTML = '';

//                 // Iterate over the received data and render it
//                 data.data.forEach((entry) => {
//                     const date = new Date(entry.date);
//                     const formattedDate = isNaN(date.getTime())
//                         ? "Invalid Date"
//                         : date.toLocaleDateString('en-US', {
//                               year: 'numeric',
//                               month: 'long',
//                               day: 'numeric',
//                           });

//                     const sent = entry.sent ?? 0;
//                     const attempted = entry.attempted ?? 0;

//                     const li = document.createElement('li');
//                     li.innerHTML = `
//                         <strong>Created</strong> <span>${sent}</span> Contacts
//                         <strong>on</strong> <span>${formattedDate}</span>
//                         <strong>Attempted to Create</strong> <span>${attempted}</span> Contacts
//                     `;
//                     ul.appendChild(li);
//                 });
//             } else {
//                 console.error('Error: No data or invalid structure received from server.');
//                 alert('Failed to load sent schedules. Please try again.');
//             }
//         })
//         .catch((error) => {
//             console.error('Error fetching sent schedules:', error);
//             alert('An error occurred while loading sent schedules.');
//         });
// }




// function sendNow() {
//     fetch('/s/scheduledsending/send-now', {
//         method: 'POST',
//         headers: {
//             'Content-Type': 'application/json',
//         },
//     })
//         .then(response => {
//             if (!response.ok) {
//                 throw new Error('Network response was not ok');
//             }
//             return response.json();
//         })
//         .then(data => {
//             if (data.success) {
//                 alert('Emails sent successfully.');
//                 console.log('Transfer Data Output:', data.details);

//                 // Refresh the page after success
//                 window.location.reload();
//             } else {
//                 alert('Error sending emails: ' + data.error);
//             }
//         })
//         .catch(error => {
//             console.error('Error triggering send now:', error);
//             alert('An error occurred: ' + error.message);
//         });
// }


//     document.addEventListener('DOMContentLoaded', function () {
//         const learnMoreButton = document.getElementById('learnMoreButton');
//         const infoBlock = document.getElementById('infoBlock');

//         learnMoreButton.addEventListener('click', function () {
//             // Toggle the visibility of the information block
//             if (infoBlock.style.display === 'none' || infoBlock.style.display === '') {
//                 infoBlock.style.display = 'block';
//                 learnMoreButton.textContent = 'Hide Info';
//                 learnMoreButton.style.backgroundColor = '#ff4d4d'; // Change button color when expanded
//             } else {
//                 infoBlock.style.display = 'none';
//                 learnMoreButton.textContent = 'Learn More';
//                 learnMoreButton.style.backgroundColor = '#0084ff'; // Revert button color
//             }
//         });
//     });
