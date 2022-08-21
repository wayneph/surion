// Listen for when the user signs-in.
const pwaAuth = document.querySelector("pwa-auth");
pwaAuth.addEventListener("signin-completed", e => {
  if (!e.detail.error) {
    // Sign-in successful!
    const email = e.detail.email;
    const name = e.detail.name;
    const imageUrl = e.detail.imageUrl;

    var date = new Date();
    date.setTime(date.getTime() + (1 * 24 * 60 * 60 * 1000));
    expires = "; expires=" + date.toGMTString();

    document.cookie = "PWAloginData = " + email + "," + name ;
    // Hide the sign-in button
    pwaAuth.style.display = "none";

    // Tell the user they're signed in
    const messageElement = document.createElement("h1");
    messageElement.textContent = "You're signed-in 😎";
    document.body.appendChild(messageElement);

    // Show their profile picture
    if (imageUrl) {
      const imageElement = document.createElement("img");
      imageElement.src = imageUrl;
      document.body.append(imageElement);
    }

    // Show their name
    const nameElement = document.createElement("h3");
    nameElement.textContent = name;
    document.body.append(nameElement);

    // Show their email
    const emailElement = document.createElement("h3");
    emailElement.textContent = email;
    document.body.append(emailElement);

    // Show how they signed in
    const providerElement = document.createElement("p");
    providerElement.textContent = `Signed in through ${e.detail.provider}`;
    document.body.append(providerElement);
  } else {
    // An error occurred
    document.body.append("Sorry -- An Authorization Error seems to have Occurred");
  }
});