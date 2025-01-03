<?php $view->extend('MauticCoreBundle:Default:content.html.php'); ?>

<?php $view['slots']->set('headerTitle', 'Scheduled Sending'); ?>

<style>
    .custom-email-schedule {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 20px;
    }

    .custom-email-schedule .email-schedule {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        padding: 20px;
        max-width: 600px;
        margin: 0 auto;
        margin-bottom: 30px;
    }

    .custom-email-schedule .button-group {
        display: flex;
        justify-content: center;
        gap: 10px;
    }

    .custom-email-schedule ul li {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .custom-email-schedule .email-schedule:last-child {
        margin-bottom: 0;
    }

.custom-email-schedule h2 {
    color: #333;
    margin-top: 0;
    margin-bottom: 15px; /* Add space below the heading */
    font-size: 24px; /* Optional: Adjust font size if needed */
}

    .custom-email-schedule ul {
        list-style: none;
        padding: 0;
    }

    .custom-email-schedule ul li {
        background: #eee;
        margin-bottom: 5px;
        padding: 10px;
        border-radius: 4px;
    }

    .custom-email-schedule button {
        margin-left: 10px;
        padding: 5px 10px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        background-color: #0084ff;
        color: white;
    }

    .custom-email-schedule button:hover {
        opacity: 0.8;
    }

    .custom-email-schedule button#saveButton {
        background-color: #4CAF50;
        color: white;
    }

    .custom-email-schedule button#saveButton:hover {
        background-color: #45a049;
    }

    #infoBlock {
        display: none;
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        text-align: left;
    }

#infoBlock > p {
    text-align: center;
    font-size: 18px; /* Increase font size for better visibility */
    font-weight: bold;
    margin-bottom: 15px;
}


#learnMoreButton {
    background-color: #007bff; /* Slightly darker blue */
    color: white;
    padding: 12px 25px; /* Larger padding for a better look */
    font-size: 16px; /* Bigger font size */
    font-weight: bold; /* Make the text bold */
    border: none;
    border-radius: 25px; /* Fully rounded button */
    transition: all 0.3s ease; /* Smooth transition */
}

#learnMoreButton:hover {
    background-color: #0056b3; /* Darker blue on hover */
    transform: scale(1.05); /* Slight scaling effect */
}



.custom-email-schedule .past-emails ul li {
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 12px 20px;
    margin-bottom: 10px;
    font-size: 14px;
    line-height: 1.5;
    display: flex;
    align-items: center;
    justify-content: space-between;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    box-sizing: border-box;
}




.info-grid {
    grid-template-columns: 1fr; /* Single column on smaller screens */
    gap: 15px; /* More space between items */
}


.info-item {
    background-color: #ffffff;
    padding: 20px; /* Increased padding for better spacing */
    border-radius: 12px; /* Larger border radius for softer edges */
    border: 1px solid #ddd;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Slightly deeper shadow for elevation */
    text-align: center; /* Center-align text */
}


.info-item:hover {
    transform: translateY(-3px); /* Slight upward motion */
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15); /* Deeper shadow on hover */
    transition: all 0.3s ease; /* Smooth transition for hover effect */
}



@media (min-width: 768px) {
    .info-grid {
        grid-template-columns: 1fr 1fr; /* Two columns for medium screens */
    }
}

@media (min-width: 1200px) {
    .info-grid {
        grid-template-columns: 1fr 1fr 1fr; /* Three columns for large screens */
    }
}
</style>

<div class="custom-email-schedule">
    <section class="email-schedule">
        <h2 style="text-align: center;">⭐️ Scheduled Contact Creation</h2>
        <div style="text-align: center; margin-bottom: 15px;">
            <button id="learnMoreButton" style="background-color: #0084ff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
                Learn More
            </button>
        </div>
        <div id="infoBlock">
            <p><strong>Before You Proceed:</strong></p>
            <div class="info-grid">
                <div class="info-item">
                    <p>The contacts you import are <strong>not immediately created</strong> inside Mautic. Instead, they are queued and scheduled for creation based on the schedules you define here.</p>
                </div>
                <div class="info-item">
                    <p>These schedules control the <strong>pace</strong> at which contacts will be created in Mautic. This helps regulate the flow of emails by limiting the number of contacts added at once.</p>
                </div>
                <div class="info-item">
                    <p>Ensure your imported contacts include a <strong>segment_name</strong> column. This column associates each contact with a segment in Mautic.</p>
                </div>
                <div class="info-item">
                    <p>Create a custom field in Mautic named <strong>segment_name</strong> if it does not already exist. This custom field will group the imported contacts into their respective segments.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="email-schedule upcoming-emails">
        <h2 style="text-align: center;">📆 Upcoming Contact Creation Schedules</h2>
        <div class="button-group" style="text-align: center; margin-bottom: 10px; margin-top: 10px;">
            <button id="addRow">Add Schedule</button>
            <button id="sendNow">Send Now</button>
            <button id="saveButton" style="background-color: #4CAF50;">Save Changes</button>
        </div>
        <ul id="emailList"></ul>
    </section>

    <section class="email-schedule past-emails">
        <h2 style="text-align: center;">📨 Past Contact Creation Logs</h2>
        <ul id="pastEmailsList"></ul>
    </section>
</div>



<script>
    document.addEventListener('DOMContentLoaded', function () {
    	setupMenuRefresh();
        initCustomEmailSchedules();
    });



    function initCustomEmailSchedules() {
        loadData();
        loadSentData();
        setupListeners();
    }


	// extremely important! without this line button listeners won't be added 
    // Also invoke directly for cases where the DOM is already loaded
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        initCustomEmailSchedules();
    }

function setupMenuRefresh() {
    const scheduledSendingMenu = document.querySelector('a[href="/s/scheduledsending"]'); // Replace with the actual selector for your menu item.

    if (scheduledSendingMenu) {
        scheduledSendingMenu.addEventListener('click', function (event) {
            event.preventDefault(); // Prevent PJAX or default behavior
            window.location.href = '/s/scheduledsending'; // Redirect and reload the page
        });
    }
}
    function setupListeners() {
        document.getElementById('addRow').addEventListener('click', addNewRow);
        document.getElementById('saveButton').addEventListener('click', saveData);
        document.getElementById('sendNow').addEventListener('click', sendNow);
    
    
    
    
        const learnMoreButton = document.getElementById('learnMoreButton');
        const infoBlock = document.getElementById('infoBlock');

        learnMoreButton.addEventListener('click', function () {
            // Toggle the visibility of the information block
            if (infoBlock.style.display === 'none' || infoBlock.style.display === '') {
                infoBlock.style.display = 'block';
                learnMoreButton.textContent = 'Hide Info';
                learnMoreButton.style.backgroundColor = '#ff4d4d'; // Change button color when expanded
            } else {
                infoBlock.style.display = 'none';
                learnMoreButton.textContent = 'Learn More';
                learnMoreButton.style.backgroundColor = '#0084ff'; // Revert button color
            }
        });
    
    
    
    }

    function addNewRow() {
        const ul = document.getElementById('emailList');
        const li = document.createElement('li');

        const numberInput = document.createElement('input');
        numberInput.type = 'number';
        numberInput.value = '500';
        numberInput.min = '1';
        numberInput.step = '1';
        numberInput.style.width = '50px';

        const deleteButton = document.createElement('button');
        deleteButton.textContent = 'Delete';
        deleteButton.addEventListener('click', () => li.remove());

        li.appendChild(numberInput);
        li.appendChild(deleteButton);
        ul.appendChild(li);
    }

function loadData() {
    fetch('/s/scheduledsending/load-schedules')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const ul = document.getElementById('emailList');
                ul.innerHTML = ''; // Clear existing list items
                data.data.forEach(number => {
                    const li = document.createElement('li');
                    const numberInput = document.createElement('input');
                    numberInput.type = 'number';
                    numberInput.value = number;
                    numberInput.min = '1';
                    numberInput.step = '1';
                    numberInput.style.width = '50px';

                    const deleteButton = document.createElement('button');
                    deleteButton.textContent = 'Delete';
                    deleteButton.addEventListener('click', () => li.remove());

                    li.appendChild(numberInput);
                    li.appendChild(deleteButton);
                    ul.appendChild(li);
                });
            } else {
                console.error('Failed to load schedules:', data.error);
                alert('Failed to load schedules. Please try again.');
            }
        })
        .catch(error => console.error('Error fetching schedules:', error));
}



function saveData() {
    const schedules = Array.from(document.querySelectorAll('#emailList li input[type="number"]'))
        .map(input => parseInt(input.value, 10))
        .filter(value => !isNaN(value));

    if (schedules.length === 0) {
        if (!confirm("You are about to save an empty schedule. Continue?")) {
            return; // If the user cancels, don't proceed
        }
    }

    fetch('/s/scheduledsending/save-schedules', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ schedules }),
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Schedules saved successfully.');
            } else {
                alert('Error saving schedules: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error saving schedules:', error);
            alert('An error occurred while saving schedules.');
        });
}

function loadSentData() {
    fetch('/s/scheduledsending/load-sent-schedules')
        .then((response) => response.json())
        .then((data) => {
            if (data.success && Array.isArray(data.data)) {
                const ul = document.getElementById('pastEmailsList');
                ul.innerHTML = '';

                // Iterate over the received data and render it
                data.data.forEach((entry) => {
                    const date = new Date(entry.date);
                    const formattedDate = isNaN(date.getTime())
                        ? "Invalid Date"
                        : date.toLocaleDateString('en-US', {
                              year: 'numeric',
                              month: 'long',
                              day: 'numeric',
                          });

                    const sent = entry.sent ?? 0;
                    const attempted = entry.attempted ?? 0;

                    const li = document.createElement('li');
                    li.innerHTML = `
                        <strong>Created</strong> <span>${sent}</span> Contacts
                        <strong>on</strong> <span>${formattedDate}</span>
                        <strong>Attempted to Create</strong> <span>${attempted}</span> Contacts
                    `;
                    ul.appendChild(li);
                });
            } else {
                console.error('Error: No data or invalid structure received from server.');
                alert('Failed to load sent schedules. Please try again.');
            }
        })
        .catch((error) => {
            console.error('Error fetching sent schedules:', error);
            alert('An error occurred while loading sent schedules.');
        });
}




function sendNow() {
    fetch('/s/scheduledsending/send-now', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Emails sent successfully.');
                console.log('Transfer Data Output:', data.details);

                // Refresh the page after success
                window.location.reload();
            } else {
                alert('Error sending emails: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error triggering send now:', error);
            alert('An error occurred: ' + error.message);
        });
}

</script>

// <script>
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
// </script>
