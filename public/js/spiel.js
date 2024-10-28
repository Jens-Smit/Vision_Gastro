const canvas = document.getElementById("gameCanvas");
const ctx = canvas.getContext("2d");

let circles = [];
let redCount = 0;
let blueCount = 0;
let gameRunning = false;

function getRandomColor() {
  return Math.random() < 0.5 ? "red" : "blue";
}

function createCircle() {
  const radius = 15;
  const x = Math.random() * (canvas.width - 2 * radius) + radius;
  const y = canvas.height + radius;
  const color = getRandomColor();
  circles.push({ x, y, radius, color });
}

function updateCircles() {
  for (let i = 0; i < circles.length; i++) {
    circles[i].y -= 0.5; //speed
    if (circles[i].y + circles[i].radius < 0) {
      circles.splice(i, 1);
      i--;
    }
  }
}

function drawCircles() {
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  for (const circle of circles) {
    ctx.beginPath();
    ctx.arc(circle.x, circle.y, circle.radius, 0, Math.PI * 2);
    ctx.fillStyle = circle.color;
    ctx.fill();
    ctx.closePath();
  }
}

function checkClick(event) {
  const rect = canvas.getBoundingClientRect();
  const mouseX = event.clientX - rect.left;
  const mouseY = event.clientY - rect.top;

  for (let i = 0; i < circles.length; i++) {
    const circle = circles[i];
    const distance = Math.sqrt(
      (mouseX - circle.x) ** 2 + (mouseY - circle.y) ** 2
    );
    if (distance < circle.radius) {
      if (circle.color === "red") {
        redCount++;
      } else {
        blueCount++;
      }
      circles.splice(i, 1);
      break;
    }
  }
}

canvas.addEventListener("click", checkClick);

function gameLoop() {
  if (gameRunning) {
    if (Math.random() < 0.025) {
      // Wahrscheinlichkeit auf 1% reduziert
      createCircle();
    }
    updateCircles();
    drawCircles();
    requestAnimationFrame(gameLoop);
  }
}

function startGame() {
  gameRunning = true;
  redCount = 0;
  blueCount = 0;
  circles = [];
  document.getElementById("PopUpCard").style.display = "block";
  gameLoop();
  setTimeout(endGame, 60000); // Spiel nach 10 Sekunden beenden
}

function endGame() {
  gameRunning = false;
  if (redCount == 3 && blueCount == 2) {
    sendForm()
    
    document.getElementById("PopUpCard").style.display = "none";
  } else {
    alert("You lose! red:" + redCount + "  blue:" + blueCount);
    document.getElementById("PopUpCard").style.display = "none";
   
  }
}

function validateField(field) {
    if (field.value === "") {
        field.style.border = "2px solid red";
    } else {
        field.style.border = "";
    }
}
document.getElementById("endGame").addEventListener("click", function () {
  endGame();
});

document.getElementById("blogForm").addEventListener("submit", function (event) {
    event.preventDefault();
    const form = event.target;
    const user = document.getElementById("postUser");
    const mail = document.getElementById("postMail");
    const comments = document.getElementById("postComments");
    if (user.value != "" && mail.value != "" && comments.value != "") {

      
      setTimeout(() => {
        //   document.getElementById('PopUpCard').style.display = 'none';
        startGame();
      }, 500); // Spiel nach 2 Sekunden starten
    } else {
        
        validateField(user);
        validateField(mail);
        validateField(comments);
    }
  });
