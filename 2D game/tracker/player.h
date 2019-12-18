#ifndef PLAYER__H
#define PLAYER__H

#include "twowaymultisprite.h"
#include <cmath>
#include <list>
#include <string>
#include "smartMultiSprite.h"
#include "drawable.h"
#include "sound.h"
#include "bullets.h"

class ExplodingSprite;

// In this example the player is derived from TwoWayMultiSprite.
// However, there are many options.
class Player : public TwoWayMultiSprite {
public:
  Player(const std::string&);
  Player(const Player&);
  ~Player();
  
  virtual void draw() const;
  virtual void update(Uint32 ticks);

  void collided() { collision = true; }
  void missed() { collision = false; }
  Player& operator=(const Player&);
  
  void advanceFrame(Uint32 ticks);
  void right();
  void left();
  void up();
  void down();
  void stop();
  void jump();
  void puff();
  bool isPoofed();
  bool getDead();
  bool isDamagable();
  void damage();
  int getInvincibleT();
  bool isExplosion();
  int getLives();
  
  void attach(SmartMultiSprite* o) {observers.push_back(o);}
  void detach(SmartMultiSprite* o);
  
protected:
  std::list<SmartMultiSprite*> observers;
  ExplodingSprite* explosion;
private:
  bool collision;
  bool isRight;
  bool isGround;
  bool isDown;
  bool isUp;
  bool isPoof;
  bool dead;
  bool damagable;
  int invulnerable;
  int iTime;
  int lives;
  Vector2f initialVelocity;
  
  std::string bulletName;
  Bullets bullets;
  float bulletSpeed;
  int bulletInterval;
  int timeSinceLastBullet;
};
#endif
