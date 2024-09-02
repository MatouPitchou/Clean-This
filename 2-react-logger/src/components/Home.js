import spongy from '../assets/spongy_dishes.svg'
import "../styles/Home.css";

function Home() {

  return (
    <div className="pageWrapper">
      <h2>Bienvenue sur Clean This Logs</h2>
      <img className='spongy' src={spongy} alt="" />
    </div>
  );
}

export default Home;
