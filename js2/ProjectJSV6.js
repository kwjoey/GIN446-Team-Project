document.addEventListener("DOMContentLoaded", () => {
  const signInButton = document.querySelector(".btn-sign-in");
  const signUpButton = document.querySelector(".btn-sign-up");
  const formContainer = document.getElementById("form-container");
  const signInForm = document.getElementById("sign-in-form");
  const signUpForm = document.getElementById("sign-up-form");
  const sellCarForm = document.getElementById("car-form");
  const closeButton = document.getElementById("close-btn");
  const sellCarButton = document.getElementById("sellCar");
  const wrapper = document.getElementById("form-wrapper");

  if (signInButton) {
    signInButton.addEventListener("click", () => {
      formContainer.style.top = "0";
      wrapper.style.marginTop = "0%";
      signInForm.style.display = "flex";
      signUpForm.style.display = "none";
    });
  }

  if (signUpButton) {
    signUpButton.addEventListener("click", () => {
      formContainer.style.top = "0";
      wrapper.style.marginTop = "0%";
      signUpForm.style.display = "flex";
      signInForm.style.display = "none";
    });
  }

  if (sellCarButton) {
    sellCarButton.addEventListener("click", () => {
      formContainer.style.top = "0";
      wrapper.style.marginTop = "30%";
      sellCarForm.style.display = "flex";
    });
  }

  function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }

  function validateForm(formId, warningId) {
    const form = document.getElementById(formId);
    const warning = document.getElementById(warningId);
    let valid = true;
    let message = "All fields must be filled.";

    form.querySelectorAll(".input-field").forEach((field) => {
      if (!field.value.trim()) {
        field.classList.add("error");
        valid = false;
      } else {
        field.classList.remove("error");
      }

      if (
        field.type === "email" &&
        field.value.trim() &&
        !isValidEmail(field.value.trim())
      ) {
        field.classList.add("error");
        valid = false;
        message = "Please enter a valid email address.";
      }
    });

    if (formId === "sign-up-form") {
      const password = document.getElementById("password-signup").value.trim();
      const confirmPassword = document
        .getElementById("confirm-password")
        .value.trim();

      if (password && confirmPassword && password !== confirmPassword) {
        document.getElementById("password-signup").classList.add("error");
        document.getElementById("confirm-password").classList.add("error");
        valid = false;
        message = "Please make sure to enter the same password.";
      }
    }

    if (!valid) {
      warning.textContent = message;
      warning.style.display = "block";
    } else {
      warning.style.display = "none";
    }

    return valid;
  }

  if (document.getElementById("sign-in-form")) {
    document
      .getElementById("sign-in-form")
      .addEventListener("submit", (event) => {
        event.preventDefault();

        if (validateForm("sign-in-form", "sign-in-warning")) {
          const username = document.getElementById("usernameSignIn").value;
          const password = document.getElementById("password-signin").value;

          const formData = new FormData();
          formData.append("usernameSignIn", username);
          formData.append("passwordSignIn", password);

          fetch("sign_in.php", {
            method: "POST",
            body: formData,
          })
            .then((response) => response.json())
            .then((data) => {
              if (data.success) {
                alert("Login successful!");
                window.location.href = "profile.php";
              } else {
                document.getElementById("sign-in-warning").textContent =
                  data.message;
                document.getElementById("sign-in-warning").style.display =
                  "block";
              }
            })
            .catch((error) => {
              console.error("Fetch error: ", error);
              document.getElementById("sign-in-warning").textContent =
                "An error occurred. Please try again.";
              document.getElementById("sign-in-warning").style.display =
                "block";
            });
        }
      });
  }

  if (document.getElementById("car-form")) {
    document.getElementById("car-form").addEventListener("submit", (event) => {
      event.preventDefault();

      if (validateForm("car-form", "sign-car-warning")) {
        const formData = new FormData(document.getElementById("car-form"));

        fetch("sell_car.php", {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              alert("Car details uploaded successfully!");
              window.location.href = "profile.php";
            } else {
              alert("Error: " + data.message);
            }
          })
          .catch((error) => {
            alert("Error: " + error.message);
          });
      }
    });
  }

  if (document.getElementById("sign-up-form")) {
    document
      .getElementById("sign-up-form")
      .addEventListener("submit", (event) => {
        event.preventDefault();
        if (validateForm("sign-up-form", "sign-up-warning")) {
          const formData = new FormData(
            document.getElementById("sign-up-form")
          );

          fetch("ProjectPHP.php", {
            method: "POST",
            body: formData,
          })
            .then((response) => response.json())
            .then((data) => {
              if (data.success) {
                alert("Sign up successful!");
                window.location.href = "profile.php";
              } else {
                document.getElementById("sign-up-warning").textContent =
                  data.message;
                document.getElementById("sign-up-warning").style.display =
                  "block";
              }
            })
            .catch((error) => {
              console.error("Fetch error: ", error);
              document.getElementById("sign-up-warning").textContent =
                "An error occurred. Please try again.";
              document.getElementById("sign-up-warning").style.display =
                "block";
            });
        }
      });
  }

  if (document.getElementById("close-btn-signin")) {
    document
      .getElementById("close-btn-signin")
      .addEventListener("click", () => {
        document.getElementById("form-container").style.top = "-100%";
      });
  }

  if (document.getElementById("close-btn-signup")) {
    document
      .getElementById("close-btn-signup")
      .addEventListener("click", () => {
        document.getElementById("form-container").style.top = "-100%";
      });
  }

  if (document.getElementById("close-btn-car")) {
    document.getElementById("close-btn-car").addEventListener("click", () => {
      document.getElementById("form-container").style.top = "-100%";
    });
  }
});

document.addEventListener("DOMContentLoaded", () => {
  const carItems = document.querySelectorAll(".car-item");
  const carPopup = document.getElementById("car-popup");
  const popupContent = document.getElementById("popup-content");
  const closePopup = document.getElementById("close-popup");
  const backdrop = document.getElementById("backdrop");

  let currentCarData = null;

  carItems.forEach((item) => {
    item.addEventListener("click", () => {
      currentCarData = JSON.parse(item.getAttribute("data-car"));

      const seller = currentCarData.seller_id;

      popupContent.innerHTML = `
  <div class="car-image-container">
    <img src="uploads/${currentCarData.car_picture}" alt="Car Picture">
    <h3>${currentCarData.car_model}</h3>
  </div>
  <div class="car-info-container">
    <p><strong>Model Year:</strong> ${currentCarData.model_year}</p>
    <p><strong>Fuel Type:</strong> ${currentCarData.fuel_type}</p>
    <p><strong>Price:</strong> $${currentCarData.price}</p>
    <p><strong>Phone:</strong> ${currentCarData.phone}</p>
    <p><strong>Location:</strong> ${currentCarData.location}</p>
    <p><strong>Post Date:</strong> ${currentCarData.post_date}</p>
    <p><strong>Description:</strong> ${currentCarData.description}</p>
  </div>
`;

      if (currentCarData.seller_id !== userId) {
        popupContent.innerHTML += `<div class="button-container">
                                   <button class="start-chat-button">📩</button>
                                   </div>
  `;
      }
      carPopup.classList.add("open");
      backdrop.classList.add("active");

      const startChatButton = popupContent.querySelector('.start-chat-button');
      if (startChatButton) {
        startChatButton.addEventListener("click", () => {
          popupContent.innerHTML = `
          <div class="chat-container">
            <div class="chat-window">
              <div id="chatHead" class="chat-header">
                <p>${currentCarData.seller_id}</p> <!-- Display seller's ID here -->
              </div>
              <div class="chat-messages" id="chatMessages">
                <!-- Messages will be displayed here -->
              </div>
              <div class="message-input-container">
                <input type="text" id="messageInput" placeholder="Type your message..." />
                <button id="sendMessageButton">Send</button>
              </div>
            </div>
          </div>
        `;

          const sendMessageButton = document.getElementById('sendMessageButton');
          const messageInput = document.getElementById('messageInput');
          const chatMessages = document.getElementById('chatMessages');

          sendMessageButton.addEventListener('click', () => {
            const message = messageInput.value.trim();
            if (message) {
              const newMessage = document.createElement('div');
              newMessage.classList.add('message');
              newMessage.textContent = message;
              chatMessages.appendChild(newMessage);

              messageInput.value = '';

              const readd = 1;
              const recieverId = currentCarData.seller_id;
              const messageText = message;
              
              fetch('send_message.php', {
                method: 'POST',
                body: new URLSearchParams({
                  'readd': readd,
                  'sender_id': userId,
                  'reciever_id': recieverId,
                  'message': messageText
                })
              })
                .then(response => response.json())
                .then(data => {
                  if (data.success) {
                    console.log("Message sent successfully");
                  } else {
                    console.error("Error sending message: " + data.message);
                  }
                })
                .catch(error => {
                  console.error("Error:", error);
                });
            }
          });
        });
      }
    });
  });

  closePopup.addEventListener("click", () => {
    carPopup.classList.remove("open");
    backdrop.classList.remove("active");
  });

  backdrop.addEventListener("click", () => {
    carPopup.classList.remove("open");
    backdrop.classList.remove("active");
  });
});