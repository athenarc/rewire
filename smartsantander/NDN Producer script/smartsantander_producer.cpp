#include "face.hpp"
#include "security/key-chain.hpp"
#include <iostream>
#include <fstream>
#include <string>
#include <stdio.h>
#include <stdlib.h>
#define CURL_STATICLIB
#include <curl/curl.h>
#include "picojson.h"

using namespace std;


typedef struct {
    char *ptr;
    size_t len;
} string_t;

void init_string(string_t *s) {
    s->len = 0;
    s->ptr = (char*)malloc(s->len + 1);

    if (s->ptr == NULL) {
        fprintf(stderr, "malloc() failed\n");

        exit(EXIT_FAILURE);
    }

    s->ptr[0] = '\0';
}

size_t write_fn(void *ptr, size_t size, size_t nmemb, string_t *s) {
    size_t new_len = s->len + (size * nmemb);
    s->ptr = (char*)realloc(s->ptr, new_len + 1);

    if (s->ptr == NULL) {
        fprintf(stderr, "realloc() failed\n");
        exit(EXIT_FAILURE);
    }

    memcpy(s->ptr+s->len, ptr, size * nmemb);
    s->ptr[new_len] = '\0';
    s->len = new_len;

    return size * nmemb;
}

// Enclosing code in ndn simplifies coding (can also use `using namespace ndn`)
namespace ndn {
// Additional nested namespace could be used to prevent/limit name contentions
namespace examples {

class Producer : noncopyable
{
public:
  void
  run()
  {
    m_face.setInterestFilter("/smartsantander/api",
                             bind(&Producer::onInterest, this, _1, _2),
                             RegisterPrefixSuccessCallback(),
                             bind(&Producer::onRegisterFailed, this, _1, _2));


    m_face.processEvents();
  }

private:
  void
  onInterest(const InterestFilter& filter, const Interest& interest)
  {
    std::cout << "<< I: " << interest << std::endl;

    // Create new name, based on Interest's name
    Name dataName(interest.getName());
    dataName
      .append("fresh") // add "testApp" component to Interest name
      .appendVersion();  // add "version" component (current UNIX timestamp in milliseconds)      

    // Get the content prefix
    std::string interestName=  (interest.getName()).toUri();


    //Send the respective curl to the server
	//api url: http://justsoft.gr/smartsantander/api/nodeid/sensor
	CURL *curl;
    CURLcode res;

    string_t s;
    init_string(&s);

    curl = curl_easy_init();

    std::string url;
    url = "http://justsoft.gr";
    url.append(interestName);
    //cout<<url<<std::endl;

	curl_easy_setopt(curl, CURLOPT_URL, url.c_str());
	curl_easy_setopt(curl, CURLOPT_FOLLOWLOCATION, 1L);
	curl_easy_setopt(curl, CURLOPT_WRITEFUNCTION, write_fn);
	curl_easy_setopt(curl, CURLOPT_WRITEDATA, &s);
	res = curl_easy_perform(curl);

	if (res != CURLE_OK) {
		fprintf(stderr, "curl_easy_perform() failed: %s\n",
		curl_easy_strerror(res));
	}

	//JSON parse
	const char *json = (const char*)s.ptr;
	picojson::value v;
	string err = picojson::get_last_error();
	picojson::parse(v, json, json + strlen(json), &err);

	if (!err.empty()) {
		cerr << err << endl;
	}

	// check if the type of the value is "object"
	if (! v.is<picojson::object>()) {
		cerr << "JSON is not an object" << endl;
		exit(2);
	}

	//printf("uom = %s\r\n" ,  v.get("uom").get<string>().c_str());
	//printf("value = %s\r\n" ,  v.get("value").get<string>().c_str());
	//printf("predicted_next_interval = %d\r\n" ,  (int)v.get("predicted_next_interval").get<double>());

	curl_easy_cleanup(curl);
  

    
    static const std::string content = v.get("value").get<string>().c_str();
    
    int fp;
    fp=(int)v.get("predicted_next_interval").get<double>();

    // Create Data packet
    //shared_ptr<Data> data = make_shared<Data>();
    //data->setName(dataName);
    //data->setFreshnessPeriod(time::seconds(fp));
    //data->setContent(reinterpret_cast<const uint8_t*>(content.data()), content.size());

	auto data = make_shared<Data>(interest.getName());
    data->setFreshnessPeriod(time::seconds(fp));
    data->setContent(reinterpret_cast<const uint8_t*>(content.data()), content.size());

    // Sign Data packet with default identity
    m_keyChain.sign(*data);
    // m_keyChain.sign(data, <identityName>);
    // m_keyChain.sign(data, <certificate>);

    // Return Data packet to the requester
    std::cout << ">> D: " << *data << std::endl;
    m_face.put(*data);
  }


  void
  onRegisterFailed(const Name& prefix, const std::string& reason)
  {
    std::cerr << "ERROR: Failed to register prefix \""
              << prefix << "\" in local hub's daemon (" << reason << ")"
              << std::endl;
    m_face.shutdown();
  }

private:
  Face m_face;
  KeyChain m_keyChain;
};

} // namespace examples
} // namespace ndn

int
main(int argc, char** argv)
{
  ndn::examples::Producer producer;
  try {
    producer.run();
  }
  catch (const std::exception& e) {
    std::cerr << "ERROR: " << e.what() << std::endl;
  }
  return 0;
}