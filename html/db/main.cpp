#include <ctime>
#include <boost/array.hpp>
#include <boost/asio.hpp>

#include "db.h"
#include "utils.h"

using boost::asio::ip::tcp;

#define MAX_BUF_SIZE 4096
#define ERR_MSG "ERROR: cannot parse query "

int main(int argc, char* argv[])
{
    try {
        // TODO: move client-server part to network.h
        // TODO: opt-args, choose port

        //load everything

        boost::asio::io_service io_service;
        tcp::acceptor acceptor(io_service, tcp::endpoint(tcp::v4(), 8888));

        std::cerr << "Server started, port 8888." << std::endl;

        for (;;) {
            tcp::socket socket(io_service);
            acceptor.accept(socket);

            char buf[MAX_BUF_SIZE];
            boost::system::error_code error;

            size_t len = socket.read_some(boost::asio::buffer(buf), error);

            if (error == boost::asio::error::eof) {
                break; // Connection closed cleanly by peer.
            } else if (error) {
                throw boost::system::system_error(error); // Some other error.
            }

            std::string answer;
            if (parse_query(buf, len)) {
                // save_to_disk
                // TODO
            } else {
                answer = ERR_MSG;
                answer += buf;
                std::cerr << answer << std::endl;
            }

            boost::system::error_code ignored_error;
            boost::asio::write(socket, boost::asio::buffer(answer), ignored_error);
        }
    }
    catch (std::exception& e)
    {
        std::cerr << e.what() << std::endl;
    }
    return 0;
}
