let GET_STARTED_BTN = null;
window.onload = function() {
    console.log("Index page loaded");

    GET_STARTED_BTN = document.getElementById("get-started-btn");
    console.log("Get Started Button:", GET_STARTED_BTN);

    GET_STARTED_BTN.onclick = function(event) {
        event.preventDefault();
        alert("Get Started button clicked!");
    }
}