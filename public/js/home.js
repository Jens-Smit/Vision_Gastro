import * as THREE from "../js/three.module.js";
import { OBJLoader } from "./OBJLoader.js" ;
import { MTLLoader } from "./MTLLoader.js";

const scene = new THREE.Scene();
const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
const renderer = new THREE.WebGLRenderer({ antialias: true });
const container = document.getElementById('threeD');
renderer.setClearColor(0x000000, 0);
renderer.setSize(container.clientWidth, container.clientHeight);
let inerW = window.innerWidth;
let inerH = window.innerHeight
container.appendChild(renderer.domElement);
const video = document.createElement('video');
video.src = '../clips/newUser.mp4';
video.loop = true;
video.muted = true;
video.play();
const videoTexture = new THREE.VideoTexture(video);
const videoMaterial = new THREE.MeshBasicMaterial({ map: videoTexture });
let iphone;
let direction = 0.0009;
const next = document.getElementById('next');
const before = document.getElementById('before');
let originalRotation, originalPosition, originalCameraZ;
function animate() {
    requestAnimationFrame(animate);
    if (iphone) {
        iphone.rotation.y += direction * 0.5;
        iphone.rotation.x += direction;
        if (iphone.rotation.x >= 0.05 || iphone.rotation.x <= -0.05) {
            direction = -direction;
        }
        
    }
    renderer.render(scene, camera);
}
//größe bei animat anpassen
 if( inerW < 600){
    camera.position.z = 1.75;  
}else{
 camera.position.z = 1.5;   
}  
originalCameraZ = camera.position.z; 
function onWindowResize() {
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(container.clientWidth, container.clientHeight);
    inerW = window.innerWidth;
    inerH = window.innerHeight;
    //infos zu rezize
    if(inerW < inerH || inerW < 600){
        camera.position.z = 2.5;  
    }else{
     camera.position.z = 1.5;   
    }
}


const mtlLoader = new MTLLoader();
mtlLoader.load('../img/iPhone 14 pro.mtl', function(materials) {
	
    materials.preload();
    const objloader = new OBJLoader();
    objloader.setMaterials(materials);
    objloader.load('../img/iPhone 14 pro.obj', function(obj) {
        iphone = obj; 
        iphone.position.set(0, -1, 0);//hochformat
        const screen = iphone.getObjectByName('iPhone_14_pro');
        screen.material[2] = videoMaterial;
        iphone.scale.set(1, 0.9, 1);
        scene.add(iphone);
        window.addEventListener('resize', onWindowResize, false); 
        animate();
    });
});
next.addEventListener('click', function() {
    before.style.display = 'block';
    next.style.display = 'none';

    

    video.src = '../clips/AreaLayout.mp4'; // Ändern Sie dies auf den Pfad Ihres neuen Videos
    video.play();

    originalRotation = iphone.rotation.z;
    originalPosition = iphone.position.clone();
    
    if (inerW < inerH || inerW < 600) {
            animateCameraZ(2.5); // Sanfte Änderung der Kamera-Position
           
        }
    // Sanfte Animation der Rotation und Position
    let clock = new THREE.Clock();
    let duration = 1; // Dauer der Animation in Sekunden
    let startRotation = iphone.rotation.z;
    let endRotation = -1.58;
    let startPosition = iphone.position.clone();
    let endPosition = new THREE.Vector3(-1, 0, 0);
    const slider = document.getElementById('image-slider');
    slider.style.transform = `translateX(-100vw)`;

    function animateTransition() {
        let elapsedTime = clock.getElapsedTime();
        let t = Math.min(elapsedTime / duration, 1); // Normalisiere die Zeit
        
        // Interpoliere die Rotation und Position
        iphone.rotation.z = THREE.MathUtils.lerp(startRotation, endRotation, t);
        iphone.position.lerpVectors(startPosition, endPosition, t);

        if (t < 1) {
            requestAnimationFrame(animateTransition);
        }
    }

    clock.start();
    animateTransition();
});
before.addEventListener('click', function() {
    before.style.display = 'none';
    next.style.display = 'block';

    
     animateCameraZ(originalCameraZ); // Sanfte Rückkehr der Kamera-Position
      

    video.src = 'newUser.mp4'; // Ändern Sie dies auf den Pfad Ihres ursprünglichen Videos
    video.play();

    // Sanfte Rückkehr zur ursprünglichen Rotation und Position
    let clock = new THREE.Clock();
    let duration = 1; // Dauer der Animation in Sekunden
    let startRotation = iphone.rotation.z;
    let endRotation = originalRotation;
    let startPosition = iphone.position.clone();
    let endPosition = originalPosition;
    const slider = document.getElementById('image-slider');
    slider.style.transform = `translateX(0)`;

    function animateTransitionBack() {
        let elapsedTime = clock.getElapsedTime();
        let t = Math.min(elapsedTime / duration, 1); // Normalisiere die Zeit
        
        // Interpoliere die Rotation und Position zurück
        iphone.rotation.z = THREE.MathUtils.lerp(startRotation, endRotation, t);
        iphone.position.lerpVectors(startPosition, endPosition, t);

        if (t < 1) {
            requestAnimationFrame(animateTransitionBack);
        }
    }

    clock.start();
    animateTransitionBack();
});

function animateCameraZ(targetZ) {
    let clock = new THREE.Clock();
    let duration = 1; // Dauer der Animation in Sekunden
    let startZ = camera.position.z;

    function animate() {
        let elapsedTime = clock.getElapsedTime();
        let t = Math.min(elapsedTime / duration, 1); // Normalisiere die Zeit
        // Interpoliere die Kamera-Position
        camera.position.z = THREE.MathUtils.lerp(startZ, targetZ, t);
        if (t < 1) {
            requestAnimationFrame(animate);
        }
    }
    clock.start();
    animate();
    
}
