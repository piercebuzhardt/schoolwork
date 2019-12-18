#include "player.h"
#include "gameData.h"
#include "renderContext.h"
#include "explodingSprite.h"
#include "bullets.h"

Player::~Player(){
	if(explosion){
		delete explosion;
	}
}

Player::Player( const std::string& name) :
  TwoWayMultiSprite(name),
  observers(),
  explosion(nullptr),
  collision(false),
  isRight(true),
  isGround(false),
  isDown(false),
  isUp(false),
  isPoof(false),
  dead(false),
  damagable(true),
  invulnerable(Gamedata::getInstance().getXmlInt(name+"/invulnerableTime")),
  iTime(0),
  lives(Gamedata::getInstance().getXmlInt(name+"/lives")),
  initialVelocity(getVelocity()),
  
  bulletName(Gamedata::getInstance().getXmlStr(name+"/bulletName")),
  bullets(bulletName),
  bulletSpeed(Gamedata::getInstance().getXmlInt(bulletName+"/speedX")),
  bulletInterval(Gamedata::getInstance().getXmlInt(bulletName+"/interval")),
  timeSinceLastBullet(0)
{ }

Player::Player(const Player& s) :
  TwoWayMultiSprite(s),
  observers(s.observers),  
  explosion(s.explosion),
  collision(s.collision),
  isRight(s.isRight),
  isGround(s.isGround),
  isDown(s.isDown),
  isUp(s.isUp),
  isPoof(s.isPoof),
  dead(s.dead),
  damagable(s.damagable),
  invulnerable(s.invulnerable),
  iTime(s.iTime),
  lives(s.lives),
  initialVelocity(s.getVelocity()),
  
  bulletName(s.bulletName),
  bullets(s.bullets),
  bulletSpeed(s.bulletSpeed),
  bulletInterval(s.bulletInterval),
  timeSinceLastBullet(s.timeSinceLastBullet)
  { }

Player& Player::operator=(const Player& s) {
  TwoWayMultiSprite::operator=(s);
  explosion = s.explosion;
  collision = s.collision;
  isRight = s.isRight;
  isGround = s.isGround;
  isDown = s.isDown;
  isUp = s.isUp;
  isPoof = s.isPoof;
  dead = s.dead;
  damagable = s.damagable;
  invulnerable = s.invulnerable;
  iTime = s.iTime;
  initialVelocity = s.initialVelocity;
  return *this;
}

void Player::detach(SmartMultiSprite* o){
	std::list<SmartMultiSprite*>::iterator ptr = observers.begin();
	while(ptr != observers.end()){
		if(*ptr == o){
			ptr = observers.erase(ptr);
			return;
		}
		++ptr;
	}
}

int Player::getInvincibleT(){
	return invulnerable - iTime;
}

bool Player::isExplosion(){
	if(explosion)
		return true;
	else
		return false;
}

bool Player::isDamagable(){
	return damagable;
}

void Player::damage(){
	damagable = false;
//	lives--;
	if(!explosion){
		Sprite sprite(getName(), getPosition(), getVelocity(), images[currentFrame]);
		sprite.setScale(getScale());
		explosion = new ExplodingSprite(sprite);
	}
}

void Player::draw() const{
	if(explosion){
		explosion->draw();
	}else{
		images[currentFrame]->draw(getX(), getY(), getScale());
	}
	bullets.draw();
}

void Player::stop() { 
  //setVelocity( Vector2f(0, 0) );
  setVelocityX( 0.6*getVelocityX() );
  if(getVelocityX() < .2 * initialVelocity[0] && getVelocityX() > -.2 * initialVelocity[0])
	  setVelocityX(0);
  if(getY() < worldHeight - getScaledHeight()){
	  if(isPoof){
		  setVelocityY(getVelocityY() + .05 * initialVelocity[1]);
		  if(getVelocityY() > .5 * initialVelocity[1])
			  setVelocityY(.5 * initialVelocity[1]);
	  }
	  else{
		  setVelocityY(getVelocityY() + .1 * initialVelocity[1]);
		  if(getVelocityY() > initialVelocity[1])
			  setVelocityY(initialVelocity[1]);
	  }
  }else{
	  setVelocityY(0);
  }
}
void Player::jump(){
	isPoof = true;
	if(getY() > 0 && !dead)
		setVelocityY(-initialVelocity[1]);
	else
		setVelocityY(0);
}

void Player::puff(){
	isPoof = false;
	setVelocityY(0);
	if(timeSinceLastBullet > bulletInterval){
		Vector2f vel = getVelocity();
		float x;
		float y = getY()+getScaledHeight()/2;
		if(isRight){
			x = getX()+getScaledWidth();
			vel[0] += bulletSpeed;
		}
		else{
			x = getX();
			vel[0] -= bulletSpeed;
		}
		bullets.shoot(Vector2f(x,y), vel);
		timeSinceLastBullet = 0;
	}
}

bool Player::isPoofed(){
	return isPoof;
}

int Player::getLives(){
	return lives;
}

bool Player::getDead(){
	return dead;
}

void Player::right() { 
  if ( getX() < worldWidth-getScaledWidth()) {
	if(!isPoof)
      setVelocityX(initialVelocity[0]);
    else
	  setVelocityX(.7 * initialVelocity[0]);
  }
  if(dead)
	  setVelocityX(0);
  else
  isRight = true;
} 
void Player::left()  { 
  if ( getX() > 0) {
	if(!isPoof)
      setVelocityX(-initialVelocity[0]);
    else
	  setVelocityX(.7 * -initialVelocity[0]);
  }
  if(dead)
	  setVelocityX(0);
  else
  isRight = false;
} 
void Player::up()    { 
/*  if ( getY() > 0) {
    setVelocityY( -initialVelocity[1] );
  }*/
  if(isGround && !dead){
    isUp = true;
  }
} 
void Player::down()  { 
/*  if ( getY() < worldHeight-getScaledHeight()) {
    setVelocityY( initialVelocity[1] );
  }*/
  if(isGround && !dead)
	isDown = true;
}

void Player::update(Uint32 ticks) { 
  timeSinceLastBullet+=ticks;
  bullets.update(ticks);
  if(lives <= 0){
	  dead = true;
  }
  if(explosion){
	  explosion->update(ticks);
/*	  std::list<SmartMultiSprite*>::iterator ptr = observers.begin();
	  while(ptr != observers.end()){
		  (*ptr)->setPlayerState(damagable);
	  }*/
	  if(explosion->chunkCount() == 0){
		  delete explosion;
		  explosion = NULL;
	  }
	  return;
  }
  
  if(!collision){
  	  advanceFrame(ticks);

	  Vector2f incr = getVelocity() * static_cast<float>(ticks) * 0.001;
	  setPosition(getPosition() + incr);

	  if ( getY() < 0) {
		setVelocityY( fabs( getVelocityY() ) );
	  }
	  if ( getY() + getScaledHeight() > worldHeight) {
		setVelocityY( -fabs( getVelocityY() ) );
		isGround = true;
	  }else{
		  isGround = false;
	  }

	  if ( getX() < 0) {
		setVelocityX( fabs( getVelocityX() ) );
	  }
	  if ( getX() + getScaledWidth() > worldWidth) {
		setVelocityX( -fabs( getVelocityX() ) );
	  }  
  }
  
  if(!damagable){
	  iTime++;
	  if(iTime >= invulnerable){
		  damagable = true;
		  iTime = 0;
	  }
  }
  
  std::list<SmartMultiSprite*>::iterator ptr = observers.begin();
  while(ptr != observers.end()){
	int offset = rand()%5;
	offset = offset*(rand()%2?-1:1);
    const Vector2f off(offset, offset);
    (*ptr)->setPlayerPos( getPosition()+off );
	(*ptr)->setPlayerState(damagable);
    ++ptr;
  }
  
  stop();
}

void Player::advanceFrame(Uint32 ticks) {
	timeSinceLastFrame += ticks;
	if (timeSinceLastFrame > frameInterval) {
		if(!dead){
			if(!isPoof){
				if(getVelocityY() > 0){
					if(isRight){
						if(currentFrame == 61){
							currentFrame = 55;
						}
						else if(currentFrame < 50 || currentFrame > 55){
							currentFrame = 50;
						}else{
							currentFrame++;
							if(currentFrame > 55)
								currentFrame = 55;
						}
					}else{
						if(currentFrame < 55 || currentFrame > 61){
							currentFrame = 56;
						}else{
							currentFrame++;
							if(currentFrame > 61 || currentFrame == 56)
								currentFrame = 61;
						}
					}
				}
				else if(getVelocityX() != 0){
					if(isGround){
						if(isRight){//walking
							if(currentFrame < 4 || currentFrame > 13){
								currentFrame = 4;
							}else{
								currentFrame = ((currentFrame + 1) % 13);
								if(currentFrame < 4 || currentFrame > 13){
									currentFrame = 4;
								}
							}
						}else{
							if(currentFrame < 18 || currentFrame > 27){
								currentFrame = 18;
							}else{
								currentFrame = ((currentFrame + 1) % 27);
								if(currentFrame < 18 || currentFrame > 27){
									currentFrame = 18;
								}
							}
						}
					}
				}
				else if(getVelocityX() == 0){
					if(isGround){
						if(isRight){
							currentFrame = 0;
							if(isDown)
								currentFrame = 1;
							if(isUp)
								currentFrame = 3;
						}else{
							currentFrame = 14;
							if(isDown)
								currentFrame = 15;
							if(isUp)
								currentFrame = 17;
						}
					}
				}
				isPoof = false;
			}else{
				if(isRight){
					if(currentFrame < 28 || (currentFrame > 38 && (currentFrame < 44 || currentFrame > 49))){
						currentFrame = 28;
					}else{
						currentFrame = ((currentFrame + 1) % 38);
						if(currentFrame < 28 || currentFrame > 38){
							currentFrame = 33;
						}
					}
				}else{
					if(((currentFrame < 33 || currentFrame > 38) && currentFrame < 39) || currentFrame > 49){
						currentFrame = 39;
					}else{
						currentFrame = ((currentFrame + 1) % 49);
						if(currentFrame < 39 || currentFrame > 49){
							currentFrame = 44;
						}
					}
				}
				isPoof = true;
			}
		}else{//when dead
			
		}
		timeSinceLastFrame = 0;
	}
	isDown = false;
	isUp = false;
}