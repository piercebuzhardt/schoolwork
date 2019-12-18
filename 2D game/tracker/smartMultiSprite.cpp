#include <functional>
#include <random>
#include <cmath>
#include "smartMultiSprite.h"
#include "gameData.h"
#include "imageFactory.h"
#include "explodingSprite.h"

void SmartMultiSprite::advanceFrame(Uint32 ticks){
	timeSinceLastFrame += ticks;
	if(timeSinceLastFrame > frameInterval){
		currentFrame = (currentFrame+1) % numberOfFrames;
		timeSinceLastFrame = 0;
	}
}

SmartMultiSprite::~SmartMultiSprite(){
	if(explosion) delete explosion;
}

float distance(float x1, float y1, float x2, float y2) {
  float x = x1-x2;
  float y = y1-y2;
  return hypot(x, y);
}

void SmartMultiSprite::reset(){
	setX(Gamedata::getInstance().getXmlInt(getName()+"/startLoc/x") + Gamedata::getInstance().getRandFloat(-500, 500));
	setY(Gamedata::getInstance().getXmlInt(getName()+"/startLoc/y") + Gamedata::getInstance().getRandFloat(-100, 100));
	
}

SmartMultiSprite::SmartMultiSprite(const std::string& name, const Vector2f& pos, int w, int h, bool state):
	Drawable(name, 
			Vector2f(Gamedata::getInstance().getXmlInt(name+"/startLoc/x") + Gamedata::getInstance().getRandFloat(-500, 500), Gamedata::getInstance().getXmlInt(name+"/startLoc/y") / 2 + Gamedata::getInstance().getRandFloat(-100, 100)),
			Vector2f(Gamedata::getInstance().getXmlInt(name+"/speedX") + Gamedata::getInstance().getRandFloat(-100, 0), Gamedata::getInstance().getXmlInt(name+"/speedY") + Gamedata::getInstance().getRandFloat(-100, 0))
			),
	images(ImageFactory::getInstance().getImages(name)),
	explosion(nullptr),
	animated(Gamedata::getInstance().getXmlBool(name+"/animated")),
	currentFrame(0),
	numberOfFrames(Gamedata::getInstance().getXmlInt(name+"/frames")),
	frameInterval(Gamedata::getInstance().getXmlInt(name+"/frameInterval")),
	timeSinceLastFrame(0),
	worldWidth(Gamedata::getInstance().getXmlInt("world/width")),
	worldHeight(Gamedata::getInstance().getXmlInt("world/height") - Gamedata::getInstance().getXmlInt("world/ground")),
	playerPos(pos),
	playerWidth(w),
	playerHeight(h),
	currentMode(NORMAL),
	safeDistance(Gamedata::getInstance().getXmlFloat(name+"/safeDistance")),
	type(),
	alert(state)
	{
		if(!animated)
			currentFrame = static_cast<int>(Gamedata::getInstance().getRandFloat(0, Gamedata::getInstance().getXmlInt(name+"/frames")-1));
		string aggro = Gamedata::getInstance().getXmlStr(name+"/type");
		if(aggro == "MEAN")
			type = MEAN;
		else if(aggro == "PASSIVE")
			type = PASSIVE;
		else
			type = PASSIVE;
	}
	
SmartMultiSprite::SmartMultiSprite(const SmartMultiSprite& f):
	Drawable(f),
	images(f.images),
	explosion(f.explosion),
	animated(f.animated),
	currentFrame(f.currentFrame),
	numberOfFrames(f.numberOfFrames),
	frameInterval(f.frameInterval),
	timeSinceLastFrame(f.timeSinceLastFrame),
	worldWidth(f.worldWidth),
	worldHeight(f.worldHeight),
	playerPos(f.playerPos),
	playerWidth(f.playerWidth),
	playerHeight(f.playerHeight),
	currentMode(f.currentMode),
	safeDistance(f.safeDistance),
	type(f.type),
	alert(f.alert)
	{}
	
SmartMultiSprite& SmartMultiSprite::operator=(const SmartMultiSprite& f){
	Drawable::operator=(f);
	images = (f.images);
	explosion = (f.explosion);
	animated = (f.animated);
	currentFrame = (f.currentFrame);
	numberOfFrames = (f.numberOfFrames);
	frameInterval = (f.frameInterval);
	timeSinceLastFrame = (f.timeSinceLastFrame);
	worldWidth = (f.worldWidth);
	worldHeight = (f.worldHeight);
	playerPos = (f.playerPos);
	playerWidth = (f.playerWidth);
	playerHeight = (f.playerHeight);
	currentMode = (f.currentMode);
	safeDistance = (f.safeDistance);
	type = (f.type);
	alert = (f.alert);
	return *this;
}

void SmartMultiSprite::explode(){
	if(!explosion){
		Sprite sprite(getName(), getPosition(), getVelocity(), images[currentFrame]);
		sprite.setScale(getScale());
		explosion = new ExplodingSprite(sprite);
	}
}

void SmartMultiSprite::goLeft(){ 
	string name = getName();
	setVelocityX(( .8 * -fabs(getVelocityX()) - .2 * Gamedata::getInstance().getXmlInt(name+"/speedX")));  
}
void SmartMultiSprite::goRight(){ 
	string name = getName();
	setVelocityX(( .8 * fabs(getVelocityX()) + .2 * Gamedata::getInstance().getXmlInt(name+"/speedX")));  
}
void SmartMultiSprite::goUp(){ 
	string name = getName();
	setVelocityY(( .8 * -fabs(getVelocityY()) - .2 * Gamedata::getInstance().getXmlInt(name+"/speedY"))); 
}
void SmartMultiSprite::goDown(){ 
	string name = getName();
	setVelocityY(( .8 * fabs(getVelocityY()) + .2 * Gamedata::getInstance().getXmlInt(name+"/speedY")));  
}

void SmartMultiSprite::draw() const{
	if(explosion)
		explosion->draw();
	else
	    images[currentFrame]->draw(getX(), getY(), getScale());
}

void SmartMultiSprite::inverse(){
	setVelocityX(-getVelocityX());
	setVelocityY(-getVelocityY());
}

bool SmartMultiSprite::isExploding(){
	if(explosion)
		return true;
	else
		return false;
}

void SmartMultiSprite::update(Uint32 ticks){
  if(explosion){
	  explosion->update(ticks);
	  if(explosion->chunkCount() == 0){
		  delete explosion;
		  explosion = NULL;
		  reset();
	  }
	  return;
  }
  
  if(animated)
	  advanceFrame(ticks);
  string name = getName();
  float x = getX()+getImage()->getWidth()/2;
  float y = getY()+getImage()->getHeight()/2;
  float ex = playerPos[0]+playerWidth/2;
  float ey = playerPos[1]+playerHeight/2;
  float distanceToEnemy = ::distance(x, y, ex, ey);
  
  Vector2f incr = getVelocity() * static_cast<float>(ticks) * 0.001;
  setPosition(getPosition() + incr);

  if(type == PASSIVE){
	  if(currentMode == NORMAL){
		  if(distanceToEnemy < safeDistance) currentMode = EVADE;
		  else if(rand() % 10000 < 3){
			  currentMode = CALM;
		  }
	  }
	  else if(currentMode == EVADE){
		  if(distanceToEnemy > safeDistance) currentMode = NORMAL;
		  else{
			  if(x < ex && getX() > 0) goLeft();
			  if(x > ex && getX() < worldWidth - getScaledWidth()) goRight();
			  if(y < ey && getY() > 0) goUp();
			  if(y > ey && getY() < worldHeight - getScaledHeight()) goDown();
		  }
	  }
	  else if(currentMode == CALM){
		  if(distanceToEnemy < safeDistance) currentMode = EVADE;
		  else{
			  setVelocityX(.9 * getVelocityX());
			  setVelocityY(.9 * getVelocityY());
		  }
	  }
  }
  if(type == MEAN){
	  if(currentMode == NORMAL){
		  if(distanceToEnemy < safeDistance) currentMode = ATTACK;
		  else if(rand() % 10000 < 2){
			  currentMode = CALM;
		  }
	  }
	  else if(currentMode == ATTACK){
		  if(distanceToEnemy > safeDistance) currentMode = NORMAL;
		  else{
			  if(alert){
				  if(x < ex && getX() < worldWidth - getScaledWidth()) goRight();
				  if(x > ex && getX() > 0) goLeft();
			      if(y < ey && getY() < worldHeight - getScaledHeight()) goDown();
			      if(y > ey && getY() > 0) goUp();
			  }
		  }
	  }
	  else if(currentMode == CALM){
		  if(distanceToEnemy < safeDistance) currentMode = ATTACK;
		  else{
			  setVelocityX(.9 * getVelocityX());
			  setVelocityY(.9 * getVelocityY());
		  }
	  }
  }
  
  if ( getY() < 0) {
    setVelocityY( fabs( getVelocityY() ) );
  }
  if ( getY() + getScaledHeight() > worldHeight) {
    setVelocityY( -fabs( getVelocityY() ) );
  }

  if ( getX() < 0) {
    setVelocityX( fabs( getVelocityX() ) );
  }
  if ( getX() + getScaledWidth() > worldWidth) {
    setVelocityX( -fabs( getVelocityX() ) );
  } 
  
  if(getVelocityY() < -Gamedata::getInstance().getXmlInt(name+"/speedY"))
	  setVelocityY(-Gamedata::getInstance().getXmlInt(name+"/speedY"));
  if(getVelocityY() > Gamedata::getInstance().getXmlInt(name+"/speedY"))
	  setVelocityY(Gamedata::getInstance().getXmlInt(name+"/speedY"));
  if(getVelocityX() < -Gamedata::getInstance().getXmlInt(name+"/speedX"))
	  setVelocityX(-Gamedata::getInstance().getXmlInt(name+"/speedX"));
  if(getVelocityX() > Gamedata::getInstance().getXmlInt(name+"/speedX"))
	  setVelocityX(Gamedata::getInstance().getXmlInt(name+"/speedX"));
}