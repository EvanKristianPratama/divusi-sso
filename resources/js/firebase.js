import { initializeApp } from "firebase/app";
import {
  getAuth,
  signInWithEmailAndPassword,
  createUserWithEmailAndPassword,
  signInWithPopup,
  GoogleAuthProvider,
  signOut,
  onAuthStateChanged,
} from "firebase/auth";

const firebaseConfig = {
  apiKey: import.meta.env.VITE_FIREBASE_API_KEY,
  authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN,
  projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID,
  storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET,
  messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID,
  appId: import.meta.env.VITE_FIREBASE_APP_ID,
  measurementId: import.meta.env.VITE_FIREBASE_MEASUREMENT_ID,
};

const app = initializeApp(firebaseConfig);
export const auth = getAuth(app);
const googleProvider = new GoogleAuthProvider();

/**
 * Register user baru ke Firebase & Laravel
 */
export async function firebaseRegister(email, password) {
  try {
    const userCredential = await createUserWithEmailAndPassword(
      auth,
      email,
      password
    );
    const idToken = await userCredential.user.getIdToken();

    // Sync ke Laravel backend
    const response = await fetch("/api/auth/register", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id_token: idToken }),
    });

    return response.json();
  } catch (error) {
    throw error;
  }
}

/**
 * Login ke Firebase & sync dengan Laravel
 */
export async function firebaseLogin(email, password) {
  try {
    const userCredential = await signInWithEmailAndPassword(
      auth,
      email,
      password
    );
    const idToken = await userCredential.user.getIdToken();

    // Send token ke Laravel backend
    const response = await fetch("/api/auth/login", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id_token: idToken }),
    });

    return response.json();
  } catch (error) {
    throw error;
  }
}

/**
 * Login dengan Google
 */
export async function firebaseLoginWithGoogle() {
  try {
    const userCredential = await signInWithPopup(auth, googleProvider);
    const idToken = await userCredential.user.getIdToken();

    // Send token ke Laravel backend
    const response = await fetch("/api/auth/login", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id_token: idToken }),
    });

    return response.json();
  } catch (error) {
    throw error;
  }
}

/**
 * Register dengan Google
 */
export async function firebaseRegisterWithGoogle() {
  try {
    const userCredential = await signInWithPopup(auth, googleProvider);
    const idToken = await userCredential.user.getIdToken();

    // Send token ke Laravel backend
    const response = await fetch("/api/auth/register", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id_token: idToken }),
    });

    return response.json();
  } catch (error) {
    throw error;
  }
}

/**
 * Logout dari Firebase & Laravel
 */
export async function firebaseLogout() {
  try {
    await signOut(auth);

    // Notify Laravel
    await fetch("/api/auth/logout", { method: "POST" });
  } catch (error) {
    throw error;
  }
}

/**
 * Monitor auth state
 */
export function onAuthChange(callback) {
  onAuthStateChanged(auth, callback);
}