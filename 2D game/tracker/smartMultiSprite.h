#ifndef SMARTMULTISPRITE__H
#define SMARTMULTISPRITE__H
#include <string>
#include <vector>
#include <cmath>
#include "multisprite.h"

class ExplodingSprite;

class SmartMultiSprite : public Drawable{
public:
	SmartMultiSprite(const std::string&, const Vector2f& pos, int w, int h, bool state);
	SmartMultiSprite(const SmartMultiSprite&);
	virtual ~SmartMultiSprite();
	
	virtual void draw() const;
	virtual void update(Uint32 ticks);
	
	virtual const Image* getImage() const { 
		return images[currentFrame]; 
	}
	int getScaledWidth()  const { 
		return getScale()*images[currentFrame]->getWidth(); 
	} 
	int getScaledHeight()  const { 
		return getScale()*images[currentFrame]->getHeight(); 
	} 
	virtual const SDL_Surface* getSurface() const { 
		return images[currentFrame]->getSurface();
	}
	
	void setPlayerPos(const Vector2f& p) {playerPos = p;}
	void setPlayerState(const bool damagable) {alert = damagable;}
	
	virtual void explode();
	virtual bool isExploding();
	void reset();
	
	void inverse();

	
protected:
  std::vector<Image *> images;
  ExplodingSprite* explosion;
  
  bool animated;
  unsigned currentFrame;
  unsigned numberOfFrames;
  unsigned frameInterval;
  float timeSinceLastFrame;
  int worldWidth;
  int worldHeight;
  
  enum AGGRESSION {PASSIVE, MEAN};
  enum MODE {NORMAL, EVADE, ATTACK, CALM};
  Vector2f playerPos;
  int playerWidth;
  int playerHeight;
  MODE currentMode;
  float safeDistance;
  AGGRESSION type;
  bool alert;
  
  void goLeft();
  void goRight();
  void goUp();
  void goDown();

  void advanceFrame(Uint32 ticks);
  SmartMultiSprite& operator=(const SmartMultiSprite&);
};
#endif